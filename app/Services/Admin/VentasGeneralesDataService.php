<?php

namespace App\Services\Admin;

use App\Models\Artesano;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Venta;
use App\Services\VentaAutoCompleteService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de supervisión de ventas para el panel administrador.
 * Incluye todas las tiendas / vendedores (no hay scope de ownership).
 */
class VentasGeneralesDataService
{
    /** Etiquetas legibles de estado (sin jerga técnica). */
    public const ESTADOS_ETIQUETA = [
        'pendiente' => 'Pendiente',
        'pendiente_activacion' => 'Esperando activación',
        'listo_pagar' => 'Lista para pagar',
        'pago_acreditado' => 'Pago acreditado',
        'en_curso' => 'En curso',
        'entregado' => 'Entregada',
        'cancelada' => 'Cancelada',
        'completada' => 'Entregada',
        'devolucion_en_proceso' => 'En devolución',
        'devuelto' => 'Devuelto',
    ];

    public const METODOS_ETIQUETA = [
        'tarjeta' => 'Tarjeta',
        'efectivo' => 'Efectivo',
    ];

    public function etiquetaEstado(?string $estado): string
    {
        $key = strtolower(trim((string) $estado));

        return self::ESTADOS_ETIQUETA[$key] ?? (filled($estado) ? (string) $estado : 'Sin estado');
    }

    public function etiquetaMetodo(?string $metodo, ?string $formaPagoNombre = null): string
    {
        $key = strtolower(trim((string) $metodo));
        if ($key !== '' && isset(self::METODOS_ETIQUETA[$key])) {
            return self::METODOS_ETIQUETA[$key];
        }
        if (filled($formaPagoNombre)) {
            return (string) $formaPagoNombre;
        }

        return 'No indicado';
    }

    /**
     * Opciones para selects de filtros (datos reales).
     *
     * @return array{tiendas: array, vendedores: array, artesanos: array, clientes: array, estados: array}
     */
    public function opcionesFiltro(): array
    {
        $tiendas = Tienda::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (Tienda $t) => ['id' => $t->id, 'nombre' => $t->nombre ?: 'Tienda #'.$t->id])
            ->all();

        $vendedores = Vendedor::query()
            ->with(['user:id,nombre,apellido_paterno,apellido_materno,email', 'tienda:id,nombre'])
            ->orderBy('id')
            ->get()
            ->map(function (Vendedor $v) {
                $nombre = $v->user?->nombre_completo ?: ('Vendedor #'.$v->id);
                $tienda = $v->tienda?->nombre;

                return [
                    'id' => $v->id,
                    'user_id' => $v->user_id,
                    'tienda_id' => $v->tienda_id,
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
            ->map(fn (User $u) => [
                'id' => $u->id,
                'nombre' => $u->nombre_completo,
            ])
            ->all();

        $estados = collect(array_keys(self::ESTADOS_ETIQUETA))
            ->reject(fn ($e) => $e === 'completada')
            ->map(fn ($e) => ['id' => $e, 'nombre' => self::ESTADOS_ETIQUETA[$e]])
            ->values()
            ->all();
        // completada ya filtrada; incluir nuevos estados admin en el filtro.

        return compact('tiendas', 'vendedores', 'artesanos', 'clientes', 'estados');
    }

    public function baseQuery(array $filtros = []): Builder
    {
        // Avanza estados vencidos antes de listar (misma fuente de verdad que la API).
        try {
            app(VentaAutoCompleteService::class)->completarVencidas();
        } catch (\Throwable) {
            // No bloquear el panel si el servicio de auto-completo falla.
        }

        $q = Venta::query()
            ->with([
                'user:id,nombre,apellido_paterno,apellido_materno,email',
                'tienda:id,nombre',
                'tienda.vendedors.user:id,nombre,apellido_paterno,apellido_materno,email',
                'forma_pago:id,nombre',
                'detalle_ventas.articulo:id,nombre,artesano_id,tienda_id,precio',
                'detalle_ventas.articulo.artesano:id,nombre',
            ])
            ->withCount('detalle_ventas');

        if (! empty($filtros['fecha_desde'])) {
            $q->whereDate('created_at', '>=', $filtros['fecha_desde']);
        }
        if (! empty($filtros['fecha_hasta'])) {
            $q->whereDate('created_at', '<=', $filtros['fecha_hasta']);
        }

        // Grupo rápido desde los cuadros superiores (admin / coherencia con métricas).
        if (! empty($filtros['grupo'])) {
            $grupo = strtolower(trim((string) $filtros['grupo']));
            match ($grupo) {
                // Ventas “reales”: no canceladas ni en proceso/resultado de devolución.
                'ventas' => $q->whereRaw(
                    "LOWER(TRIM(COALESCE(estado, ''))) NOT IN (
                        'cancelada', 'cancelado', 'devolucion_en_proceso', 'devuelto'
                    )"
                ),
                'entregadas' => $q->whereIn('estado', ['entregado', 'completada']),
                'en_proceso' => $q->whereIn('estado', [
                    'pendiente',
                    'pendiente_activacion',
                    'listo_pagar',
                    'pago_acreditado',
                    'en_curso',
                ]),
                'canceladas' => $q->whereIn('estado', ['cancelada', 'cancelado']),
                // Devoluciones: en curso + ya devueltas.
                'devoluciones' => $q->whereIn('estado', ['devolucion_en_proceso', 'devuelto']),
                // Monto: mismas filas que suman dinero (sin canceladas ni devueltas).
                'monto' => $q->soloIngresoValido(),
                default => null,
            };
        } elseif (! empty($filtros['en_proceso'])) {
            // Pedidos que aún no terminan (ni entregados ni cancelados ni devolución).
            $q->whereIn('estado', [
                'pendiente',
                'pendiente_activacion',
                'listo_pagar',
                'pago_acreditado',
                'en_curso',
            ]);
        } elseif (! empty($filtros['estado'])) {
            $estado = strtolower(trim((string) $filtros['estado']));
            if ($estado === 'entregado') {
                $q->whereIn('estado', ['entregado', 'completada']);
            } elseif ($estado === 'devoluciones') {
                $q->whereIn('estado', ['devolucion_en_proceso', 'devuelto']);
            } else {
                $q->where('estado', $estado);
            }
        }

        if (! empty($filtros['tienda_id'])) {
            $q->where('tienda_id', (int) $filtros['tienda_id']);
        }

        if (! empty($filtros['vendedor_id'])) {
            $vendedorId = (int) $filtros['vendedor_id'];
            $q->whereHas('tienda.vendedors', fn (Builder $vq) => $vq->where('id', $vendedorId));
        }

        if (! empty($filtros['cliente_id'])) {
            $q->where('user_id', (int) $filtros['cliente_id']);
        }

        if (! empty($filtros['artesano_id'])) {
            $artesanoId = (int) $filtros['artesano_id'];
            $q->whereHas(
                'detalle_ventas.articulo',
                fn (Builder $aq) => $aq->where('artesano_id', $artesanoId)
            );
        }

        if (! empty($filtros['metodo_pago'])) {
            $q->where('metodo_pago', strtolower(trim((string) $filtros['metodo_pago'])));
        }

        if (isset($filtros['monto_min']) && $filtros['monto_min'] !== '' && $filtros['monto_min'] !== null) {
            $q->where('total', '>=', (float) $filtros['monto_min']);
        }
        if (isset($filtros['monto_max']) && $filtros['monto_max'] !== '' && $filtros['monto_max'] !== null) {
            $q->where('total', '<=', (float) $filtros['monto_max']);
        }

        if (! empty($filtros['busqueda'])) {
            $term = trim((string) $filtros['busqueda']);
            $like = $this->likeOperator();
            $q->where(function (Builder $w) use ($term, $like) {
                if (ctype_digit($term)) {
                    $w->orWhere('id', (int) $term);
                }
                $w->orWhere('codigo_barras', $like, "%{$term}%")
                    ->orWhereHas('user', function (Builder $uq) use ($term, $like) {
                        $uq->where('nombre', $like, "%{$term}%")
                            ->orWhere('apellido_paterno', $like, "%{$term}%")
                            ->orWhere('apellido_materno', $like, "%{$term}%")
                            ->orWhere('email', $like, "%{$term}%");
                    })
                    ->orWhereHas('tienda', fn (Builder $tq) => $tq->where('nombre', $like, "%{$term}%"))
                    ->orWhereHas(
                        'detalle_ventas.articulo',
                        fn (Builder $aq) => $aq->where('nombre', $like, "%{$term}%")
                    );
            });
        }

        if (! empty($filtros['reseñada'])) {
            $flag = strtolower((string) $filtros['reseñada']);
            if ($flag === 'si') {
                $q->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('detalle_ventas')
                        ->join('resenas', 'resenas.articulo_id', '=', 'detalle_ventas.articulo_id')
                        ->whereColumn('detalle_ventas.venta_id', 'ventas.id')
                        ->whereColumn('resenas.user_id', 'ventas.user_id');
                });
            } elseif ($flag === 'no') {
                $q->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('detalle_ventas')
                        ->join('resenas', 'resenas.articulo_id', '=', 'detalle_ventas.articulo_id')
                        ->whereColumn('detalle_ventas.venta_id', 'ventas.id')
                        ->whereColumn('resenas.user_id', 'ventas.user_id');
                });
            }
        }

        return $q;
    }

    public function aplicarOrden(Builder $q, string $orden = 'fecha_desc'): Builder
    {
        return match ($orden) {
            'fecha_asc' => $q->orderBy('created_at')->orderBy('id'),
            'monto_desc' => $q->orderByDesc('total')->orderByDesc('id'),
            'monto_asc' => $q->orderBy('total')->orderByDesc('id'),
            'estado' => $q->orderBy('estado')->orderByDesc('created_at'),
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
     * Resumen del conjunto filtrado (no solo la página actual).
     *
     * @return array{
     *   total_compras: int,
     *   monto_total: float,
     *   entregadas: int,
     *   canceladas: int,
     *   en_proceso: int,
     *   top_vendedores: array<int, array{nombre: string, compras: int, monto: float}>
     * }
     */
    public function resumen(array $filtros = []): array
    {
        $base = $this->baseQuery($filtros);

        // Conteos por categoría (misma lógica que el filtro rápido de cada cuadro).
        // Sin aplicar "grupo" del cuadro activo: las métricas salen del filtro de fechas/búsqueda etc.
        // Nota: $base ya puede incluir grupo; para tarjetas coherentes usamos la base sin grupo.
        $filtrosSinGrupo = $filtros;
        unset($filtrosSinGrupo['grupo'], $filtrosSinGrupo['estado'], $filtrosSinGrupo['en_proceso']);
        $baseMetricas = $this->baseQuery($filtrosSinGrupo);

        $totalRegistros = (clone $baseMetricas)->count();

        // Ventas = operaciones activas (no canceladas ni devoluciones).
        $ventas = (clone $baseMetricas)->whereRaw(
            "LOWER(TRIM(COALESCE(estado, ''))) NOT IN (
                'cancelada', 'cancelado', 'devolucion_en_proceso', 'devuelto'
            )"
        )->count();

        // Monto total = solo ingreso válido (excluye cancelada y devuelto).
        // “En devolución” sigue contando hasta pasar a “Devuelto”.
        $montoTotal = (float) (clone $baseMetricas)->soloIngresoValido()->sum('total');

        $entregadas = (clone $baseMetricas)->whereIn('estado', ['entregado', 'completada'])->count();
        $canceladas = (clone $baseMetricas)->whereIn('estado', ['cancelada', 'cancelado'])->count();
        $devoluciones = (clone $baseMetricas)->whereIn('estado', ['devolucion_en_proceso', 'devuelto'])->count();
        $devueltas = (clone $baseMetricas)->where('estado', 'devuelto')->count();
        $enProceso = (clone $baseMetricas)->whereIn('estado', [
            'pendiente',
            'pendiente_activacion',
            'listo_pagar',
            'pago_acreditado',
            'en_curso',
        ])->count();

        // Ranking por monto de ingreso válido (no inflado por canceladas/devueltas).
        $top = (clone $baseMetricas)
            ->soloIngresoValido()
            ->select('tienda_id', DB::raw('COUNT(*) as compras'), DB::raw('SUM(total) as monto'))
            ->groupBy('tienda_id')
            ->orderByDesc('monto')
            ->limit(5)
            ->get();

        $tiendaIds = $top->pluck('tienda_id')->filter()->all();
        $tiendas = Tienda::query()
            ->with(['vendedors.user:id,nombre,apellido_paterno,apellido_materno'])
            ->whereIn('id', $tiendaIds)
            ->get()
            ->keyBy('id');

        $topVendedores = $top->map(function ($row) use ($tiendas) {
            $tienda = $tiendas->get($row->tienda_id);
            $vendedor = $tienda?->vendedors?->first()?->user?->nombre_completo;
            $nombre = $vendedor
                ? $vendedor.($tienda?->nombre ? ' · '.$tienda->nombre : '')
                : ($tienda?->nombre ?: 'Tienda #'.$row->tienda_id);

            return [
                'nombre' => $nombre,
                'compras' => (int) $row->compras,
                'monto' => round((float) $row->monto, 2),
            ];
        })->values()->all();

        return [
            'total_compras' => $totalRegistros,
            'ventas' => $ventas,
            'monto_total' => round($montoTotal, 2),
            'entregadas' => $entregadas,
            'canceladas' => $canceladas,
            'devoluciones' => $devoluciones,
            'devueltas' => $devueltas,
            'en_proceso' => $enProceso,
            'top_vendedores' => $topVendedores,
        ];
    }

    public function mapearFila(Venta $venta): array
    {
        $detalles = $venta->detalle_ventas ?? collect();
        $productos = $detalles->map(function ($d) {
            $art = $d->articulo;

            return [
                'articulo_id' => $d->articulo_id,
                'nombre' => $art?->nombre ?: ('Prenda #'.$d->articulo_id),
                'cantidad' => (int) $d->cantidad,
                'precio_unitario' => (float) $d->precio_unitario,
                'subtotal' => (float) $d->subtotal,
                'artesano' => $art?->artesano?->nombre,
            ];
        })->values()->all();

        $nombresProductos = collect($productos)->pluck('nombre')->filter()->implode(', ');
        $cantidadTotal = (int) collect($productos)->sum('cantidad');

        $vendedorUser = $venta->tienda?->vendedors?->first()?->user;
        $artesanos = collect($productos)->pluck('artesano')->filter()->unique()->values()->implode(', ');

        $tieneResena = $this->ventaTieneResenaCliente($venta);
        $estado = strtolower(trim((string) $venta->estado));
        $acciones = app(AdminVentaAccionesService::class);

        return [
            'id' => $venta->id,
            'referencia' => 'CMP-'.str_pad((string) $venta->id, 5, '0', STR_PAD_LEFT),
            'fecha' => $this->formatearFecha($venta->created_at),
            'fecha_iso' => $venta->created_at?->toDateString(),
            'cliente' => $venta->user?->nombre_completo ?: 'Cliente no disponible',
            'cliente_email' => $venta->user?->email,
            'cliente_id' => $venta->user_id,
            'vendedor' => $vendedorUser?->nombre_completo ?: '—',
            'tienda' => $venta->tienda?->nombre ?: '—',
            'tienda_id' => $venta->tienda_id,
            'artesanos' => $artesanos !== '' ? $artesanos : '—',
            'productos' => $productos,
            'productos_resumen' => $nombresProductos !== '' ? $nombresProductos : 'Sin prendas',
            'cantidad_total' => $cantidadTotal,
            'lineas' => (int) ($venta->detalle_ventas_count ?? count($productos)),
            'total' => round((float) $venta->total, 2),
            'estado' => $estado,
            'estado_etiqueta' => $this->etiquetaEstado($venta->estado),
            'metodo' => $this->etiquetaMetodo($venta->metodo_pago, $venta->forma_pago?->nombre),
            'codigo_barras' => $venta->codigo_barras,
            'tiene_resena' => $tieneResena,
            'puede_cancelar' => $acciones->sePuedeCancelar($estado),
            'puede_devolver' => $acciones->sePuedeIniciarDevolucion($estado),
            'admin_nota' => $venta->admin_nota,
            // Fechas opcionales: no encadenar ->format() tras un null (rompia todo el listado).
            'admin_accion_at' => $this->formatearFecha($venta->admin_accion_at),
            'next_state_at' => $this->formatearFecha($venta->next_state_at),
            'next_state_at_iso' => $venta->next_state_at?->toIso8601String(),
        ];
    }

    /**
     * Formatea fechas de forma segura (null → null, sin excepciones en cadena).
     *
     * Evita el patrón `optional($f)?->timezone()->format()` que rompe cuando $f es null
     * (timezone devuelve null y format se llama sobre null).
     */
    protected function formatearFecha(mixed $fecha, string $formato = 'd/m/Y H:i'): ?string
    {
        if ($fecha === null) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($fecha)
                ->timezone(config('app.timezone'))
                ->format($formato);
        } catch (\Throwable) {
            return null;
        }
    }

    public function detalle(int $id): ?array
    {
        $venta = Venta::query()
            ->with([
                'user:id,nombre,apellido_paterno,apellido_materno,email,telefono',
                'tienda:id,nombre,descripcion',
                'tienda.vendedors.user:id,nombre,apellido_paterno,apellido_materno,email',
                'forma_pago:id,nombre',
                'adminUser:id,nombre,apellido_paterno,apellido_materno,email',
                'detalle_ventas.articulo:id,nombre,artesano_id,tienda_id,precio,region',
                'detalle_ventas.articulo.artesano:id,nombre',
                'detalle_ventas.articulo.resenas' => function ($q) {
                    $q->with('user:id,nombre,apellido_paterno,apellido_materno,email');
                },
            ])
            ->find($id);

        if (! $venta) {
            return null;
        }

        $fila = $this->mapearFila($venta);

        $resenasRelacionadas = [];
        foreach ($venta->detalle_ventas as $detalle) {
            $art = $detalle->articulo;
            if (! $art) {
                continue;
            }
            foreach ($art->resenas ?? [] as $resena) {
                // Priorizar reseñas del comprador de esta venta; mostrar también otras del producto.
                $resenasRelacionadas[] = [
                    'id' => $resena->id,
                    'del_comprador' => (int) $resena->user_id === (int) $venta->user_id,
                    'autor' => $resena->user?->nombre_completo ?: 'Usuario',
                    'calificacion' => (int) $resena->calificacion,
                    'comentario' => (string) ($resena->comentario ?? ''),
                    'producto' => $art->nombre,
                    'fecha' => optional($resena->created_at)?->format('d/m/Y H:i'),
                ];
            }
        }

        usort($resenasRelacionadas, function ($a, $b) {
            if ($a['del_comprador'] === $b['del_comprador']) {
                return $b['id'] <=> $a['id'];
            }

            return $a['del_comprador'] ? -1 : 1;
        });

        $fila['resenas'] = $resenasRelacionadas;
        $fila['cliente_telefono'] = $venta->user?->telefono;
        $fila['tienda_descripcion'] = $venta->tienda?->descripcion;
        $fila['admin_nombre'] = $venta->adminUser?->nombre_completo;

        return $fila;
    }

    /**
     * @param  Collection<int, Venta>  $ventas
     * @return Collection<int, array>
     */
    public function mapearColeccion(Collection $ventas): Collection
    {
        return $ventas
            ->map(function (Venta $v) {
                try {
                    return $this->mapearFila($v);
                } catch (\Throwable $e) {
                    // Una fila defectuosa no debe vaciar todo el listado.
                    \Illuminate\Support\Facades\Log::warning(
                        'No se pudo mapear venta #'.$v->id.' para el listado admin: '.$e->getMessage()
                    );

                    return null;
                }
            })
            ->filter()
            ->values();
    }

    protected function ventaTieneResenaCliente(Venta $venta): bool
    {
        $articuloIds = ($venta->detalle_ventas ?? collect())->pluck('articulo_id')->filter()->all();
        if ($articuloIds === [] || ! $venta->user_id) {
            return false;
        }

        return DB::table('resenas')
            ->where('user_id', $venta->user_id)
            ->whereIn('articulo_id', $articuloIds)
            ->exists();
    }

    protected function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
