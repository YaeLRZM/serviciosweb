<?php

namespace App\Services\Admin;

use App\Models\Artesano;
use App\Models\Resena;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Venta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de supervisión de reseñas para el panel administrador.
 */
class ResenasDataService
{
    /**
     * @return array{tiendas: array, vendedores: array, artesanos: array, clientes: array, calificaciones: array}
     */
    public function opcionesFiltro(): array
    {
        $tiendas = Tienda::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (Tienda $t) => ['id' => $t->id, 'nombre' => $t->nombre ?: 'Tienda #'.$t->id])
            ->all();

        $vendedores = Vendedor::query()
            ->with(['user:id,nombre,apellido_paterno,apellido_materno', 'tienda:id,nombre'])
            ->orderBy('id')
            ->get()
            ->map(function (Vendedor $v) {
                $nombre = $v->user?->nombre_completo ?: ('Vendedor #'.$v->id);
                $tienda = $v->tienda?->nombre;

                return [
                    'id' => $v->id,
                    'nombre' => $tienda ? "{$nombre} · {$tienda}" : $nombre,
                ];
            })
            ->all();

        $artesanos = Artesano::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (Artesano $a) => ['id' => $a->id, 'nombre' => $a->nombre ?: 'Artesano #'.$a->id])
            ->all();

        $clientes = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'user'))
            ->orderBy('nombre')
            ->limit(500)
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'email'])
            ->map(fn (User $u) => ['id' => $u->id, 'nombre' => $u->nombre_completo])
            ->all();

        $calificaciones = [
            ['id' => '1', 'nombre' => '1 estrella'],
            ['id' => '2', 'nombre' => '2 estrellas'],
            ['id' => '3', 'nombre' => '3 estrellas'],
            ['id' => '4', 'nombre' => '4 estrellas'],
            ['id' => '5', 'nombre' => '5 estrellas'],
            ['id' => 'baja', 'nombre' => 'Bajas (1–2)'],
            ['id' => 'media', 'nombre' => 'Medias (3)'],
            ['id' => 'alta', 'nombre' => 'Altas (4–5)'],
        ];

        return compact('tiendas', 'vendedores', 'artesanos', 'clientes', 'calificaciones');
    }

    public function baseQuery(array $filtros = []): Builder
    {
        $q = Resena::query()
            ->with([
                'user:id,nombre,apellido_paterno,apellido_materno,email',
                'articulo:id,nombre,tienda_id,artesano_id',
                'articulo.tienda:id,nombre',
                'articulo.tienda.vendedors.user:id,nombre,apellido_paterno,apellido_materno',
                'articulo.artesano:id,nombre',
            ]);

        if (! empty($filtros['fecha_desde'])) {
            $q->whereDate('created_at', '>=', $filtros['fecha_desde']);
        }
        if (! empty($filtros['fecha_hasta'])) {
            $q->whereDate('created_at', '<=', $filtros['fecha_hasta']);
        }

        if (! empty($filtros['calificacion'])) {
            $cal = (string) $filtros['calificacion'];
            if ($cal === 'baja') {
                $q->whereIn('calificacion', [1, 2]);
            } elseif ($cal === 'media') {
                $q->where('calificacion', 3);
            } elseif ($cal === 'alta') {
                $q->whereIn('calificacion', [4, 5]);
            } elseif (ctype_digit($cal)) {
                $q->where('calificacion', (int) $cal);
            }
        }

        if (! empty($filtros['cliente_id'])) {
            $q->where('user_id', (int) $filtros['cliente_id']);
        }

        if (! empty($filtros['tienda_id'])) {
            $tiendaId = (int) $filtros['tienda_id'];
            $q->whereHas('articulo', fn (Builder $aq) => $aq->where('tienda_id', $tiendaId));
        }

        if (! empty($filtros['vendedor_id'])) {
            $vendedorId = (int) $filtros['vendedor_id'];
            $q->whereHas(
                'articulo.tienda.vendedors',
                fn (Builder $vq) => $vq->where('id', $vendedorId)
            );
        }

        if (! empty($filtros['artesano_id'])) {
            $artesanoId = (int) $filtros['artesano_id'];
            $q->whereHas('articulo', fn (Builder $aq) => $aq->where('artesano_id', $artesanoId));
        }

        if (! empty($filtros['producto'])) {
            $term = trim((string) $filtros['producto']);
            $like = $this->likeOperator();
            $q->whereHas('articulo', fn (Builder $aq) => $aq->where('nombre', $like, "%{$term}%"));
        }

        if (! empty($filtros['compra_id'])) {
            $compraId = (int) $filtros['compra_id'];
            // Relación indirecta: el autor reseñó un artículo que compró en esa venta.
            $q->whereExists(function ($sub) use ($compraId) {
                $sub->select(DB::raw(1))
                    ->from('detalle_ventas')
                    ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
                    ->whereColumn('detalle_ventas.articulo_id', 'resenas.articulo_id')
                    ->whereColumn('ventas.user_id', 'resenas.user_id')
                    ->where('ventas.id', $compraId);
            });
        }

        if (! empty($filtros['busqueda'])) {
            $term = trim((string) $filtros['busqueda']);
            $like = $this->likeOperator();
            $q->where(function (Builder $w) use ($term, $like) {
                $w->where('comentario', $like, "%{$term}%")
                    ->orWhereHas('user', function (Builder $uq) use ($term, $like) {
                        $uq->where('nombre', $like, "%{$term}%")
                            ->orWhere('apellido_paterno', $like, "%{$term}%")
                            ->orWhere('apellido_materno', $like, "%{$term}%")
                            ->orWhere('email', $like, "%{$term}%");
                    })
                    ->orWhereHas('articulo', fn (Builder $aq) => $aq->where('nombre', $like, "%{$term}%"))
                    ->orWhereHas('articulo.tienda', fn (Builder $tq) => $tq->where('nombre', $like, "%{$term}%"))
                    ->orWhereHas('articulo.artesano', fn (Builder $aq) => $aq->where('nombre', $like, "%{$term}%"));
            });
        }

        if (! empty($filtros['recientes']) && $filtros['recientes'] === 'si') {
            $q->where('created_at', '>=', now()->subDays(14));
        }

        return $q;
    }

    public function aplicarOrden(Builder $q, string $orden = 'fecha_desc'): Builder
    {
        return match ($orden) {
            'fecha_asc' => $q->orderBy('created_at')->orderBy('id'),
            'calificacion_desc' => $q->orderByDesc('calificacion')->orderByDesc('created_at'),
            'calificacion_asc' => $q->orderBy('calificacion')->orderByDesc('created_at'),
            default => $q->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    public function paginar(array $filtros = [], int $perPage = 12): LengthAwarePaginator
    {
        $orden = (string) ($filtros['orden'] ?? 'fecha_desc');
        $q = $this->aplicarOrden($this->baseQuery($filtros), $orden);

        return $q->paginate($perPage)->withQueryString();
    }

    /**
     * @return array{
     *   total: int,
     *   promedio: float|null,
     *   bajas: int,
     *   altas: int,
     *   recientes: int
     * }
     */
    public function resumen(array $filtros = []): array
    {
        $base = $this->baseQuery($filtros);
        $total = (clone $base)->count();
        $promedio = $total > 0 ? round((float) (clone $base)->avg('calificacion'), 2) : null;
        $bajas = (clone $base)->whereIn('calificacion', [1, 2])->count();
        $altas = (clone $base)->whereIn('calificacion', [4, 5])->count();
        $recientes = (clone $base)->where('created_at', '>=', now()->subDays(14))->count();

        return compact('total', 'promedio', 'bajas', 'altas', 'recientes');
    }

    public function mapearFila(Resena $resena): array
    {
        $art = $resena->articulo;
        $tienda = $art?->tienda;
        $vendedor = $tienda?->vendedors?->first()?->user;

        $comprasRelacionadas = $this->comprasRelacionadas(
            (int) $resena->user_id,
            (int) $resena->articulo_id
        );

        return [
            'id' => $resena->id,
            'autor' => $resena->user?->nombre_completo ?: 'Usuario',
            'autor_email' => $resena->user?->email,
            'autor_id' => $resena->user_id,
            'fecha' => optional($resena->created_at)?->timezone(config('app.timezone'))->format('d/m/Y H:i'),
            'calificacion' => (int) $resena->calificacion,
            'comentario' => trim((string) ($resena->comentario ?? '')),
            'producto' => $art?->nombre ?: ('Prenda #'.$resena->articulo_id),
            'producto_id' => $resena->articulo_id,
            'tienda' => $tienda?->nombre ?: '—',
            'tienda_id' => $art?->tienda_id,
            'vendedor' => $vendedor?->nombre_completo ?: '—',
            'artesano' => $art?->artesano?->nombre ?: '—',
            'compras' => $comprasRelacionadas,
            'compra_principal' => $comprasRelacionadas[0] ?? null,
        ];
    }

    public function detalle(int $id): ?array
    {
        $resena = Resena::query()
            ->with([
                'user:id,nombre,apellido_paterno,apellido_materno,email,telefono',
                'articulo:id,nombre,tienda_id,artesano_id,descripcion,region',
                'articulo.tienda:id,nombre,descripcion',
                'articulo.tienda.vendedors.user:id,nombre,apellido_paterno,apellido_materno,email',
                'articulo.artesano:id,nombre',
            ])
            ->find($id);

        if (! $resena) {
            return null;
        }

        return $this->mapearFila($resena);
    }

    /**
     * Compras del autor que incluyen el artículo reseñado.
     *
     * @return array<int, array{id: int, referencia: string, fecha: string|null, estado: string, total: float}>
     */
    protected function comprasRelacionadas(int $userId, int $articuloId): array
    {
        if ($userId <= 0 || $articuloId <= 0) {
            return [];
        }

        $ventas = Venta::query()
            ->where('user_id', $userId)
            ->whereHas('detalle_ventas', fn (Builder $q) => $q->where('articulo_id', $articuloId))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'estado', 'total', 'created_at']);

        $svc = app(VentasGeneralesDataService::class);

        return $ventas->map(fn (Venta $v) => [
            'id' => $v->id,
            'referencia' => 'CMP-'.str_pad((string) $v->id, 5, '0', STR_PAD_LEFT),
            'fecha' => optional($v->created_at)?->format('d/m/Y'),
            'estado' => $svc->etiquetaEstado($v->estado),
            'total' => round((float) $v->total, 2),
        ])->all();
    }

    protected function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
