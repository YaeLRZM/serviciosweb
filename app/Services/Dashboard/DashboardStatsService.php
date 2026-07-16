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
     * Clientes activos = users distintos con al menos 1 venta completada en el periodo.
     * Ventas realizadas = conteo de ventas con estado = completada en el periodo.
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
     * Vendedores activos con más ventas completadas en su tienda.
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
                    ->where('ve.estado', '=', 'completada');
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
     * Alertas operativas reales (no hay tabla de reportes/moderación).
     *
     * @return list<array{tipo:string,entidad:string,motivo:string,urgente:bool,count:int}>
     */
    public function alertasOperativas(): array
    {
        $vendedoresInactivos = Vendedor::query()->where('estatus', 'inactivo')->count();
        $articulosAgotados = Articulo::query()
            ->where(function ($q) {
                $q->whereDoesntHave('inventario')
                    ->orWhereHas('inventario', fn ($i) => $i->where('stock_actual', '<=', 0));
            })
            ->count();
        $ventasPendientes = Venta::query()->where('estado', 'pendiente')->count();
        $stockBajo = Inventario::query()
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('stock_actual', '>', 0)
            ->count();

        $alertas = [];

        if ($vendedoresInactivos > 0) {
            $alertas[] = [
                'tipo' => 'Vendedores inactivos',
                'entidad' => 'vendedor',
                'motivo' => "Hay {$vendedoresInactivos} vendedor(es) con estatus inactivo pendientes de revisión.",
                'urgente' => $vendedoresInactivos >= 3,
                'count' => $vendedoresInactivos,
            ];
        }

        if ($articulosAgotados > 0) {
            $alertas[] = [
                'tipo' => 'Artículos agotados',
                'entidad' => 'publicacion',
                'motivo' => "Hay {$articulosAgotados} artículo(s) sin existencias (stock 0 o sin inventario).",
                'urgente' => $articulosAgotados >= 10,
                'count' => $articulosAgotados,
            ];
        }

        if ($stockBajo > 0) {
            $alertas[] = [
                'tipo' => 'Stock bajo mínimo',
                'entidad' => 'publicacion',
                'motivo' => "Hay {$stockBajo} inventario(s) con stock_actual ≤ stock_minimo.",
                'urgente' => false,
                'count' => $stockBajo,
            ];
        }

        if ($ventasPendientes > 0) {
            $alertas[] = [
                'tipo' => 'Ventas pendientes',
                'entidad' => 'publicacion',
                'motivo' => "Hay {$ventasPendientes} venta(s) en estado pendiente.",
                'urgente' => $ventasPendientes >= 5,
                'count' => $ventasPendientes,
            ];
        }

        return $alertas;
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function clientesActivosEntre(Carbon $desde, Carbon $hasta): int
    {
        return (int) Venta::query()
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$desde, $hasta])
            ->distinct('user_id')
            ->count('user_id');
    }

    private function ventasCompletadasEntre(Carbon $desde, Carbon $hasta): int
    {
        return (int) Venta::query()
            ->where('estado', 'completada')
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
