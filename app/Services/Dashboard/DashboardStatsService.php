<?php

namespace App\Services\Dashboard;

use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Vendedor;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Métricas del dashboard admin desde PostgreSQL (Eloquent/SQL local).
 * Sin mocks ni self-HTTP.
 *
 * Fuente real de ventas: tablas ventas + detalle_ventas (+ articulos, artesanos, tiendas, users, vendedors).
 * Nota: la tabla "compras" no existe en el schema pgsql actual.
 */
class DashboardStatsService
{
    /**
     * Clientes activos = users distintos con al menos 1 venta entregada en el periodo.
     * Ventas realizadas = conteo de ventas con estado = entregado en el periodo.
     *
     * @return array{clientes_activos:int, clientes_crecimiento:string, ventas:int, ventas_crecimiento:string}
     */
    public function resumenGeneral(string $periodo): array
    {
        [$desde, $hasta] = $this->rangoFechas($periodo);
        [$desdeAnterior, $hastaAnterior] = $this->rangoAnterior($periodo);

        $clientesActivos = $this->clientesActivosEntre($desde, $hasta);
        $clientesAnterior = $this->clientesActivosEntre($desdeAnterior, $hastaAnterior);

        $ventas = $this->ventasCompletadasEntre($desde, $hasta);
        $ventasAnterior = $this->ventasCompletadasEntre($desdeAnterior, $hastaAnterior);

        return [
            'clientes_activos' => $clientesActivos,
            'clientes_crecimiento' => $this->calcularCrecimiento($clientesAnterior, $clientesActivos),
            'ventas' => $ventas,
            'ventas_crecimiento' => $this->calcularCrecimiento($ventasAnterior, $ventas),
        ];
    }

    /**
     * Top productos por unidades vendidas (detalle_ventas + articulos + artesanos).
     *
     * @return list<array{id:int,nombre:string,region:string,artesano:string,precio_unitario:float,cantidad_vendida:int,total_vendido:float}>
     */
    public function productosPopularesDesdeBd(int $limite = 3): array
    {
        return $this->topProductosVendidosDesdeBd($limite);
    }

    /**
     * @return list<array{id:int,nombre:string,region:string,artesano:string,precio_unitario:float,cantidad_vendida:int,total_vendido:float}>
     */
    public function topProductosVendidosDesdeBd(int $limite = 20): array
    {
        $limite = max(1, $limite);

        $filas = DetalleVenta::query()
            ->from('detalle_ventas as d')
            ->join('articulos as a', 'a.id', '=', 'd.articulo_id')
            ->leftJoin('artesanos as ar', 'ar.id', '=', 'a.artesano_id')
            ->select([
                'd.articulo_id',
                'a.nombre',
                'a.region',
                'ar.nombre as artesano',
                DB::raw('SUM(d.cantidad) as cantidad_vendida'),
                DB::raw('AVG(d.precio_unitario) as precio_unitario'),
                DB::raw('SUM(d.subtotal) as total_vendido'),
            ])
            ->groupBy('d.articulo_id', 'a.nombre', 'a.region', 'ar.nombre')
            ->orderByDesc('cantidad_vendida')
            ->limit($limite)
            ->get();

        return $filas->map(fn ($fila) => [
            'id' => (int) $fila->articulo_id,
            'nombre' => (string) ($fila->nombre ?? 'Producto eliminado'),
            'region' => (string) ($fila->region ?? '—'),
            'artesano' => (string) ($fila->artesano ?? '—'),
            'precio_unitario' => (float) $fila->precio_unitario,
            'cantidad_vendida' => (int) $fila->cantidad_vendida,
            'total_vendido' => (float) $fila->total_vendido,
        ])->values()->all();
    }

    /**
     * Ventas (unidades) agrupadas por articulos.region.
     * Filtro opcional por categoria_id o nombre de categoría; "Todos" = sin filtro.
     *
     * @return list<array{region:string,ventas:int,top_prenda:string}>
     */
    public function ventasPorRegion(?string $categoriaFiltro = null): array
    {
        $query = DetalleVenta::query()
            ->from('detalle_ventas as d')
            ->join('articulos as a', 'a.id', '=', 'd.articulo_id')
            ->select([
                'a.region',
                DB::raw('SUM(d.cantidad) as ventas'),
            ])
            ->whereNotNull('a.region')
            ->where('a.region', '!=', '');

        if ($categoriaFiltro && $categoriaFiltro !== 'Todos') {
            if (is_numeric($categoriaFiltro)) {
                $query->where('a.categoria_id', (int) $categoriaFiltro);
            } else {
                $query->join('categorias as c', 'c.id', '=', 'a.categoria_id')
                    ->where('c.nombre', $categoriaFiltro);
            }
        }

        $porRegion = $query
            ->groupBy('a.region')
            ->orderByDesc('ventas')
            ->limit(12)
            ->get();

        if ($porRegion->isEmpty()) {
            return [];
        }

        // Top prenda por región (1 query auxiliar con window).
        $tops = collect(DB::select("
            SELECT region, nombre FROM (
                SELECT a.region, a.nombre,
                       SUM(d.cantidad) AS qty,
                       ROW_NUMBER() OVER (PARTITION BY a.region ORDER BY SUM(d.cantidad) DESC) AS rn
                FROM detalle_ventas d
                JOIN articulos a ON a.id = d.articulo_id
                GROUP BY a.region, a.nombre
            ) t
            WHERE rn = 1
        "))->keyBy('region');

        return $porRegion->map(fn ($fila) => [
            'region' => (string) $fila->region,
            'ventas' => (int) $fila->ventas,
            'top_prenda' => (string) ($tops[$fila->region]->nombre ?? '—'),
        ])->values()->all();
    }

    /**
     * Categorías reales para el filtro del gráfico.
     *
     * @return list<array{id:int,nombre:string}>
     */
    public function categoriasFiltro(): array
    {
        return Categoria::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (Categoria $c) => [
                'id' => (int) $c->id,
                'nombre' => (string) $c->nombre,
            ])
            ->values()
            ->all();
    }

    /**
     * Vendedores activos con más ventas entregadas en su tienda.
     *
     * @return list<array{id:int,nombre:string,tienda:string,foto_url:string,ventas:int}>
     */
    public function vendedoresDestacados(int $limite = 4): array
    {
        $limite = max(1, $limite);

        $filas = Vendedor::query()
            ->from('vendedors as v')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->leftJoin('tiendas as t', 't.id', '=', 'v.tienda_id')
            ->leftJoin('ventas as ve', function ($join) {
                $join->on('ve.tienda_id', '=', 'v.tienda_id')
                    ->where('ve.estado', 'entregado');
            })
            ->where('v.estatus', 'activo')
            ->select([
                'v.id',
                'u.nombre',
                'u.apellido_paterno',
                'u.apellido_materno',
                'u.email',
                't.nombre as tienda',
                DB::raw('COUNT(ve.id) as ventas'),
            ])
            ->groupBy('v.id', 'u.nombre', 'u.apellido_paterno', 'u.apellido_materno', 'u.email', 't.nombre')
            ->orderByDesc('ventas')
            ->limit($limite)
            ->get();

        return $filas->map(function ($fila) {
            $partes = array_filter([
                $fila->nombre,
                $fila->apellido_paterno,
                $fila->apellido_materno,
            ]);
            $nombre = trim(implode(' ', $partes));
            if ($nombre === '') {
                $nombre = (string) ($fila->email ?? 'Vendedor');
            }

            return [
                'id' => (int) $fila->id,
                'nombre' => $nombre,
                'tienda' => (string) ($fila->tienda ?? 'Sin tienda'),
                'foto_url' => 'https://ui-avatars.com/api/?name=' . urlencode($nombre) . '&background=D81B60&color=fff&rounded=true',
                'ventas' => (int) $fila->ventas,
            ];
        })->values()->all();
    }

    /**
     * Alertas operativas reales y accionables (solo si hay algo que revisar).
     * Cada alerta incluye `url` hacia la vista filtrada correspondiente.
     *
     * @return list<array{
     *   id:string,
     *   tipo:string,
     *   motivo:string,
     *   urgente:bool,
     *   prioridad:int,
     *   count:int,
     *   etiqueta:string,
     *   url:string
     * }>
     */
    public function alertasOperativas(): array
    {
        $alertas = [];
        $desde14 = now()->subDays(14)->startOfDay();
        $desde30 = now()->subDays(30)->startOfDay();
        $fechaDesde14 = $desde14->toDateString();
        $fechaDesde30 = $desde30->toDateString();

        // 1) Compras canceladas recientes
        $canceladas14 = (int) Venta::query()
            ->where('estado', 'cancelada')
            ->where('created_at', '>=', $desde14)
            ->count();
        if ($canceladas14 > 0) {
            $alertas[] = [
                'id' => 'canceladas_recientes',
                'tipo' => 'Compras canceladas recientemente',
                'motivo' => $canceladas14 === 1
                    ? 'Se detectó 1 compra cancelada en los últimos 14 días. Conviene revisar el motivo y el historial.'
                    : "Se detectaron {$canceladas14} compras canceladas en los últimos 14 días. Revisa el listado para aclarar inconvenientes.",
                'urgente' => $canceladas14 >= 5,
                'prioridad' => 100 + min($canceladas14, 50),
                'count' => $canceladas14,
                'etiqueta' => 'Compras',
                'url' => route('admin.ventas.index', [
                    'estado' => 'cancelada',
                    'fecha_desde' => $fechaDesde14,
                ]),
            ];
        }

        // 2) Proporción alta de cancelaciones (30 días)
        $total30 = (int) Venta::query()->where('created_at', '>=', $desde30)->count();
        $canceladas30 = (int) Venta::query()
            ->where('estado', 'cancelada')
            ->where('created_at', '>=', $desde30)
            ->count();
        if ($total30 >= 5 && $canceladas30 >= 3) {
            $ratio = (int) round(($canceladas30 / $total30) * 100);
            if ($ratio >= 20) {
                $alertas[] = [
                    'id' => 'tasa_cancelacion',
                    'tipo' => 'Cancelaciones en aumento',
                    'motivo' => "En los últimos 30 días, el {$ratio}% de las compras quedaron canceladas ({$canceladas30} de {$total30}). Revisa el patrón.",
                    'urgente' => $ratio >= 35,
                    'prioridad' => 95 + min($ratio, 40),
                    'count' => $canceladas30,
                    'etiqueta' => 'Compras',
                    'url' => route('admin.ventas.index', [
                        'estado' => 'cancelada',
                        'fecha_desde' => $fechaDesde30,
                    ]),
                ];
            }
        }

        // 3) Reseñas con baja calificación recientes
        $resenasBajas = (int) \App\Models\Resena::query()
            ->whereIn('calificacion', [1, 2])
            ->where('created_at', '>=', $desde14)
            ->count();
        if ($resenasBajas > 0) {
            $alertas[] = [
                'id' => 'resenas_bajas',
                'tipo' => 'Reseñas recientes con baja calificación',
                'motivo' => $resenasBajas === 1
                    ? 'Hay 1 reseña reciente con 1 o 2 estrellas. Revisa el comentario y la prenda relacionada.'
                    : "Hay {$resenasBajas} reseñas recientes con 1 o 2 estrellas. Conviene revisar inconformidades de clientes.",
                'urgente' => $resenasBajas >= 5,
                'prioridad' => 90 + min($resenasBajas, 30),
                'count' => $resenasBajas,
                'etiqueta' => 'Reseñas',
                'url' => route('admin.resenas.index', [
                    'calificacion' => 'baja',
                    'fecha_desde' => $fechaDesde14,
                ]),
            ];
        }

        // 4) Pedidos que aún no terminan (flujo de pago / entrega)
        $enProceso = (int) Venta::query()
            ->whereIn('estado', [
                'pendiente',
                'pendiente_activacion',
                'listo_pagar',
                'pago_acreditado',
                'en_curso',
            ])
            ->count();
        if ($enProceso > 0) {
            $alertas[] = [
                'id' => 'pedidos_en_proceso',
                'tipo' => 'Pedidos que requieren seguimiento',
                'motivo' => $enProceso === 1
                    ? 'Hay 1 pedido que aún no está entregado ni cancelado. Revisa su estado actual.'
                    : "Hay {$enProceso} pedidos que aún no finalizan. Revisa si alguno lleva demasiado tiempo en proceso.",
                'urgente' => $enProceso >= 8,
                'prioridad' => 80 + min($enProceso, 25),
                'count' => $enProceso,
                'etiqueta' => 'Compras',
                // Sin estado único: abrir ventas ordenadas por más antiguas + 30 días
                'url' => route('admin.ventas.index', [
                    'fecha_desde' => $fechaDesde30,
                    'orden' => 'fecha_asc',
                    'en_proceso' => '1',
                ]),
            ];
        }

        // 5) Publicaciones sin existencias
        $agotados = (int) Articulo::query()
            ->where(function ($q) {
                $q->where('stock', '<=', 0)
                    ->orWhereHas('inventario', fn ($i) => $i->where('stock_actual', '<=', 0));
            })
            ->count();
        if ($agotados > 0) {
            $alertas[] = [
                'id' => 'productos_agotados',
                'tipo' => 'Prendas sin existencias',
                'motivo' => $agotados === 1
                    ? 'Hay 1 prenda marcada sin existencias. Revisa publicaciones y stock.'
                    : "Hay {$agotados} prendas sin existencias. Revisa el catálogo de publicaciones.",
                'urgente' => $agotados >= 15,
                'prioridad' => 55 + min($agotados, 20),
                'count' => $agotados,
                'etiqueta' => 'Publicaciones',
                'url' => route('admin.publicacion.index', ['filtro_stock' => 'agotado']),
            ];
        }

        // 6) Stock bajo mínimo (solo si hay filas reales)
        $stockBajo = (int) Inventario::query()
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('stock_actual', '>', 0)
            ->count();
        if ($stockBajo > 0) {
            $alertas[] = [
                'id' => 'stock_bajo',
                'tipo' => 'Existencias por reponer',
                'motivo' => $stockBajo === 1
                    ? 'Hay 1 prenda con existencias por debajo del mínimo definido.'
                    : "Hay {$stockBajo} prendas con existencias por debajo del mínimo definido.",
                'urgente' => false,
                'prioridad' => 40 + min($stockBajo, 15),
                'count' => $stockBajo,
                'etiqueta' => 'Publicaciones',
                'url' => route('admin.publicacion.index', ['filtro_stock' => 'bajo']),
            ];
        }

        // 7) Vendedores inactivos
        $vendedoresInactivos = (int) Vendedor::query()->where('estatus', 'inactivo')->count();
        if ($vendedoresInactivos > 0) {
            $alertas[] = [
                'id' => 'vendedores_inactivos',
                'tipo' => 'Vendedores inactivos por revisar',
                'motivo' => $vendedoresInactivos === 1
                    ? 'Hay 1 vendedor marcado como inactivo. Confirma si su cuenta debe reactivarse o mantenerse así.'
                    : "Hay {$vendedoresInactivos} vendedores inactivos. Revisa su situación en el listado de vendedores.",
                'urgente' => $vendedoresInactivos >= 3,
                'prioridad' => 50 + min($vendedoresInactivos, 20),
                'count' => $vendedoresInactivos,
                'etiqueta' => 'Vendedores',
                'url' => route('admin.vendedores.index', ['estatus' => 'inactivo']),
            ];
        }

        // 8) Publicaciones ocultas (no disponibles)
        $ocultas = (int) Articulo::query()->where('disponible', false)->count();
        if ($ocultas > 0) {
            $alertas[] = [
                'id' => 'publicaciones_ocultas',
                'tipo' => 'Publicaciones ocultas',
                'motivo' => $ocultas === 1
                    ? 'Hay 1 prenda oculta del catálogo. Revisa si debe volver a mostrarse.'
                    : "Hay {$ocultas} prendas ocultas del catálogo. Revisa si alguna se ocultó por error.",
                'urgente' => false,
                'prioridad' => 30 + min($ocultas, 15),
                'count' => $ocultas,
                'etiqueta' => 'Publicaciones',
                'url' => route('admin.publicacion.index', ['filtro_visible' => 'ocultas']),
            ];
        }

        usort($alertas, function (array $a, array $b) {
            if (($a['urgente'] ?? false) !== ($b['urgente'] ?? false)) {
                return ($b['urgente'] ?? false) <=> ($a['urgente'] ?? false);
            }

            return ($b['prioridad'] ?? 0) <=> ($a['prioridad'] ?? 0);
        });

        return array_values($alertas);
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function clientesActivosEntre(Carbon $desde, Carbon $hasta): int
    {
        return (int) Venta::query()
            ->where('estado', 'entregado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->distinct('user_id')
            ->count('user_id');
    }

    private function ventasCompletadasEntre(Carbon $desde, Carbon $hasta): int
    {
        return (int) Venta::query()
            ->where('estado', 'entregado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->count();
    }

    private function rangoFechas(string $periodo): array
    {
        return match ($periodo) {
            'semana' => [now()->subDays(7), now()],
            'mes' => [now()->startOfMonth(), now()],
            default => [now()->subYears(5), now()],
        };
    }

    private function rangoAnterior(string $periodo): array
    {
        return match ($periodo) {
            'semana' => [now()->subDays(14), now()->subDays(7)],
            'mes' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            default => [now()->subYears(10), now()->subYears(5)],
        };
    }

    private function calcularCrecimiento(int $anterior, int $actual): string
    {
        if ($anterior === 0) {
            return $actual > 0 ? '+100%' : '0%';
        }

        $variacion = (($actual - $anterior) / $anterior) * 100;
        $signo = $variacion >= 0 ? '+' : '';

        return $signo . round($variacion) . '%';
    }
}
