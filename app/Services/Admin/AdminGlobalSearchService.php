<?php

namespace App\Services\Admin;

use App\Models\Artesano;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Resena;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Búsqueda global del panel administrador (datos reales).
 */
class AdminGlobalSearchService
{
    /**
     * @return array{
     *   query: string,
     *   total: int,
     *   groups: list<array{tipo:string,etiqueta:string,items:list<array{id:string,titulo:string,subtitulo:string,url:string}>}>
     * }
     */
    public function buscar(string $query, ?string $rutaActual = null, int $porTipo = 5): array
    {
        $q = trim($query);
        if (mb_strlen($q) < 2) {
            return ['query' => $q, 'total' => 0, 'groups' => []];
        }

        $like = $this->likeOperator();
        $term = "%{$q}%";
        $porTipo = max(3, min(8, $porTipo));

        $groups = [
            'compras' => $this->buscarCompras($q, $term, $like, $porTipo),
            'prendas' => $this->buscarPrendas($term, $like, $porTipo),
            'vendedores' => $this->buscarVendedores($term, $like, $porTipo),
            'usuarios' => $this->buscarUsuarios($term, $like, $porTipo),
            'tiendas' => $this->buscarTiendas($term, $like, $porTipo),
            'artesanos' => $this->buscarArtesanos($term, $like, $porTipo),
            'categorias' => $this->buscarCategorias($term, $like, $porTipo),
            'resenas' => $this->buscarResenas($term, $like, $porTipo),
        ];

        // Priorizar según la vista actual.
        $orden = $this->ordenPorRuta($rutaActual);
        $out = [];
        foreach ($orden as $key) {
            if (empty($groups[$key]['items'])) {
                continue;
            }
            $out[] = $groups[$key];
        }

        $total = collect($out)->sum(fn ($g) => count($g['items']));

        return [
            'query' => $q,
            'total' => $total,
            'groups' => $out,
        ];
    }

    /**
     * @return list<string>
     */
    protected function ordenPorRuta(?string $ruta): array
    {
        $base = ['compras', 'prendas', 'vendedores', 'usuarios', 'tiendas', 'artesanos', 'categorias', 'resenas'];

        $primero = match (true) {
            str_contains((string) $ruta, 'ventas') => 'compras',
            str_contains((string) $ruta, 'resenas') => 'resenas',
            str_contains((string) $ruta, 'publicacion') => 'prendas',
            str_contains((string) $ruta, 'vendedor') => 'vendedores',
            str_contains((string) $ruta, 'usuario') => 'usuarios',
            str_contains((string) $ruta, 'categoria') => 'categorias',
            str_contains((string) $ruta, 'artesano') => 'artesanos',
            default => null,
        };

        if ($primero === null) {
            return $base;
        }

        return array_values(array_unique(array_merge([$primero], $base)));
    }

    protected function buscarCompras(string $q, string $term, string $like, int $limit): array
    {
        $query = Venta::query()
            ->with(['user:id,nombre,apellido_paterno,apellido_materno,email', 'tienda:id,nombre'])
            ->orderByDesc('id');

        if (preg_match('/^(?:CMP-?)?(\d+)$/i', $q, $m)) {
            $query->where(function ($w) use ($m, $term, $like) {
                $w->where('id', (int) $m[1])
                    ->orWhere('codigo_barras', $like, $term)
                    ->orWhereHas('user', function ($uq) use ($term, $like) {
                        $uq->where('nombre', $like, $term)
                            ->orWhere('apellido_paterno', $like, $term)
                            ->orWhere('email', $like, $term);
                    })
                    ->orWhereHas('tienda', fn ($tq) => $tq->where('nombre', $like, $term));
            });
        } else {
            $query->where(function ($w) use ($term, $like) {
                $w->where('codigo_barras', $like, $term)
                    ->orWhereHas('user', function ($uq) use ($term, $like) {
                        $uq->where('nombre', $like, $term)
                            ->orWhere('apellido_paterno', $like, $term)
                            ->orWhere('apellido_materno', $like, $term)
                            ->orWhere('email', $like, $term);
                    })
                    ->orWhereHas('tienda', fn ($tq) => $tq->where('nombre', $like, $term))
                    ->orWhereHas('detalle_ventas.articulo', fn ($aq) => $aq->where('nombre', $like, $term));
            });
        }

        $items = $query->limit($limit)->get()->map(function (Venta $v) {
            $ref = 'CMP-'.str_pad((string) $v->id, 5, '0', STR_PAD_LEFT);
            $cliente = $v->user?->nombre_completo ?: 'Cliente';
            $tienda = $v->tienda?->nombre ?: 'Tienda';
            $estado = app(VentasGeneralesDataService::class)->etiquetaEstado($v->estado);

            return [
                'id' => 'compra-'.$v->id,
                'titulo' => "{$ref} · {$cliente}",
                'subtitulo' => "{$tienda} · {$estado} · \$".number_format((float) $v->total, 2),
                'url' => route('admin.ventas.index', ['busqueda' => (string) $v->id]),
            ];
        })->all();

        return ['tipo' => 'compras', 'etiqueta' => 'Compras', 'items' => $items];
    }

    protected function buscarPrendas(string $term, string $like, int $limit): array
    {
        $items = Articulo::query()
            ->with(['tienda:id,nombre', 'artesano:id,nombre'])
            ->where(function ($w) use ($term, $like) {
                $w->where('nombre', $like, $term)
                    ->orWhere('region', $like, $term)
                    ->orWhereHas('tienda', fn ($t) => $t->where('nombre', $like, $term))
                    ->orWhereHas('artesano', fn ($a) => $a->where('nombre', $like, $term));
            })
            ->orderBy('nombre')
            ->limit($limit)
            ->get()
            ->map(function (Articulo $a) {
                $extra = collect([
                    $a->tienda?->nombre,
                    $a->artesano?->nombre,
                    $a->region,
                ])->filter()->implode(' · ');

                return [
                    'id' => 'prenda-'.$a->id,
                    'titulo' => $a->nombre ?: 'Prenda #'.$a->id,
                    'subtitulo' => $extra !== '' ? $extra : 'Prenda del catálogo',
                    'url' => route('admin.publicacion.index', ['busqueda' => $a->nombre ?: (string) $a->id]),
                ];
            })->all();

        return ['tipo' => 'prendas', 'etiqueta' => 'Prendas', 'items' => $items];
    }

    protected function buscarVendedores(string $term, string $like, int $limit): array
    {
        $items = Vendedor::query()
            ->with(['user:id,nombre,apellido_paterno,apellido_materno,email', 'tienda:id,nombre'])
            ->where(function ($w) use ($term, $like) {
                $w->where('codigo_ine', $like, $term)
                    ->orWhereHas('user', function ($uq) use ($term, $like) {
                        $uq->where('nombre', $like, $term)
                            ->orWhere('apellido_paterno', $like, $term)
                            ->orWhere('apellido_materno', $like, $term)
                            ->orWhere('email', $like, $term);
                    })
                    ->orWhereHas('tienda', fn ($t) => $t->where('nombre', $like, $term));
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (Vendedor $v) {
                $nombre = $v->user?->nombre_completo ?: 'Vendedor #'.$v->id;
                $tienda = $v->tienda?->nombre ?: 'Sin tienda';
                $estatus = $v->estatus ? ucfirst((string) $v->estatus) : '';

                return [
                    'id' => 'vendedor-'.$v->id,
                    'titulo' => $nombre,
                    'subtitulo' => trim($tienda.($estatus !== '' ? " · {$estatus}" : '')),
                    'url' => route('admin.vendedores.index', ['busqueda' => $v->user?->email ?: $nombre]),
                ];
            })->all();

        return ['tipo' => 'vendedores', 'etiqueta' => 'Vendedores', 'items' => $items];
    }

    protected function buscarUsuarios(string $term, string $like, int $limit): array
    {
        $items = User::query()
            ->with('roles')
            ->where(function ($w) use ($term, $like) {
                $w->where('nombre', $like, $term)
                    ->orWhere('apellido_paterno', $like, $term)
                    ->orWhere('apellido_materno', $like, $term)
                    ->orWhere('email', $like, $term);
            })
            ->orderBy('nombre')
            ->limit($limit)
            ->get()
            ->map(function (User $u) {
                $rol = $u->getRoleNames()->first() ?: 'sin rol';
                $rolLabel = match ($rol) {
                    'admin' => 'Administrador',
                    'vendedor' => 'Vendedor',
                    'user' => 'Cliente',
                    default => $rol,
                };

                return [
                    'id' => 'usuario-'.$u->id,
                    'titulo' => $u->nombre_completo,
                    'subtitulo' => "{$u->email} · {$rolLabel}",
                    'url' => route('admin.usuarios.index', ['busqueda' => $u->email]),
                ];
            })->all();

        return ['tipo' => 'usuarios', 'etiqueta' => 'Usuarios', 'items' => $items];
    }

    protected function buscarTiendas(string $term, string $like, int $limit): array
    {
        $items = Tienda::query()
            ->where(function ($w) use ($term, $like) {
                $w->where('nombre', $like, $term)
                    ->orWhere('descripcion', $like, $term);
            })
            ->orderBy('nombre')
            ->limit($limit)
            ->get()
            ->map(fn (Tienda $t) => [
                'id' => 'tienda-'.$t->id,
                'titulo' => $t->nombre ?: 'Tienda #'.$t->id,
                'subtitulo' => Str::limit((string) ($t->descripcion ?? 'Tienda del catálogo'), 80),
                'url' => route('admin.ventas.index', ['tienda_id' => $t->id]),
            ])->all();

        return ['tipo' => 'tiendas', 'etiqueta' => 'Tiendas', 'items' => $items];
    }

    protected function buscarArtesanos(string $term, string $like, int $limit): array
    {
        $items = Artesano::query()
            ->where('nombre', $like, $term)
            ->orderBy('nombre')
            ->limit($limit)
            ->get()
            ->map(fn (Artesano $a) => [
                'id' => 'artesano-'.$a->id,
                'titulo' => $a->nombre ?: 'Artesano #'.$a->id,
                'subtitulo' => 'Artesano del catálogo',
                'url' => route('admin.artesanos.index'),
            ])->all();

        return ['tipo' => 'artesanos', 'etiqueta' => 'Artesanos', 'items' => $items];
    }

    protected function buscarCategorias(string $term, string $like, int $limit): array
    {
        $items = Categoria::query()
            ->where(function ($w) use ($term, $like) {
                $w->where('nombre', $like, $term);
                if (\Illuminate\Support\Facades\Schema::hasColumn('categorias', 'descripcion')) {
                    $w->orWhere('descripcion', $like, $term);
                }
            })
            ->orderBy('nombre')
            ->limit($limit)
            ->get()
            ->map(fn (Categoria $c) => [
                'id' => 'categoria-'.$c->id,
                'titulo' => $c->nombre ?: 'Categoría #'.$c->id,
                'subtitulo' => 'Categoría del catálogo',
                'url' => route('admin.categorias.index'),
            ])->all();

        return ['tipo' => 'categorias', 'etiqueta' => 'Categorías', 'items' => $items];
    }

    protected function buscarResenas(string $term, string $like, int $limit): array
    {
        $items = Resena::query()
            ->with(['user:id,nombre,apellido_paterno,apellido_materno', 'articulo:id,nombre'])
            ->where(function ($w) use ($term, $like) {
                $w->where('comentario', $like, $term)
                    ->orWhereHas('articulo', fn ($a) => $a->where('nombre', $like, $term))
                    ->orWhereHas('user', function ($uq) use ($term, $like) {
                        $uq->where('nombre', $like, $term)
                            ->orWhere('apellido_paterno', $like, $term)
                            ->orWhere('email', $like, $term);
                    });
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (Resena $r) {
                $autor = $r->user?->nombre_completo ?: 'Cliente';
                $prenda = $r->articulo?->nombre ?: 'Prenda';
                $comentario = trim((string) ($r->comentario ?? ''));

                return [
                    'id' => 'resena-'.$r->id,
                    'titulo' => "{$autor} · {$r->calificacion}/5",
                    'subtitulo' => $prenda.($comentario !== '' ? ' · '.Str::limit($comentario, 50) : ''),
                    'url' => route('admin.resenas.index', ['busqueda' => $prenda]),
                ];
            })->all();

        return ['tipo' => 'resenas', 'etiqueta' => 'Reseñas', 'items' => $items];
    }

    protected function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
