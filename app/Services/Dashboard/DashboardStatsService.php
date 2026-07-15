<?php

namespace App\Services\Dashboard;

use App\Models\Articulo;
use App\Models\Compra;
use App\Services\Api\ArticuloApiService;
use App\Services\Api\CompraApiService;
use App\Services\Api\UsuarioApiService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    public function __construct(
        protected UsuarioApiService $usuarios,
        protected CompraApiService $compras,
        protected ArticuloApiService $articulos,
    ) {}

    public function resumenGeneral(string $periodo): array
    {
        $compras = $this->obtenerColeccion($this->compras->all());

        [$desde, $hasta] = $this->rangoFechas($periodo);
        [$desdeAnterior, $hastaAnterior] = $this->rangoAnterior($periodo);

        $comprasPeriodo = $this->filtrarPorFecha($compras, $desde, $hasta);
        $comprasAnterior = $this->filtrarPorFecha($compras, $desdeAnterior, $hastaAnterior);

        // Supuesto: "cliente activo" = cliente distinto con al menos 1 compra en el periodo.
        // Ajustar si el negocio maneja otro criterio (ej. Usuarios.Estatus = 'Activo').
        $clientesActivos = collect($comprasPeriodo)->pluck('user_id')->unique()->count();
        $clientesActivosAnterior = collect($comprasAnterior)->pluck('user_id')->unique()->count();

        return [
            'clientes_activos' => $clientesActivos,
            'clientes_crecimiento' => $this->calcularCrecimiento($clientesActivosAnterior, $clientesActivos),
            'ventas' => count($comprasPeriodo),
            'ventas_crecimiento' => $this->calcularCrecimiento(count($comprasAnterior), count($comprasPeriodo)),
        ];
    }

    public function productosPopulares(int $limite = 3): array
    {
        return $this->calcularTopProductos($limite);
    }

    public function top20ProductosVendidos(): array
    {
        return $this->calcularTopProductos(20);
    }

    /**
     * Top productos desde BD local (sin HTTP / sin ApiClient).
     * Evita self-request a artisan serve en el dashboard admin.
     */
    public function productosPopularesDesdeBd(int $limite = 3): array
    {
        return $this->topProductosVendidosDesdeBd($limite);
    }

    /**
     * Top N productos vendidos agregando compras en SQL (sin HTTP).
     * Misma forma de array que calcularTopProductos() (ruta API).
     *
     * @return array<int, array{id:int,nombre:string,region:string,artesano:string,precio_unitario:float,cantidad_vendida:int,total_vendido:float}>
     */
    public function topProductosVendidosDesdeBd(int $limite = 20): array
    {
        $limite = max(1, $limite);

        // 1 query de agregación + 1 query de nombres (sin N+1).
        $filas = Compra::query()
            ->select([
                'articulo_id',
                DB::raw('SUM(cantidad) as cantidad_vendida'),
                DB::raw('SUM(cantidad * precio_unitario) as total_vendido'),
                DB::raw('AVG(precio_unitario) as precio_unitario'),
            ])
            ->groupBy('articulo_id')
            ->orderByDesc('cantidad_vendida')
            ->limit($limite)
            ->get();

        if ($filas->isEmpty()) {
            return [];
        }

        $nombres = Articulo::query()
            ->whereIn('id', $filas->pluck('articulo_id'))
            ->pluck('nombre', 'id');

        return $filas->map(function ($fila) use ($nombres) {
            $articuloId = (int) $fila->articulo_id;

            return [
                'id' => $articuloId,
                'nombre' => (string) ($nombres[$articuloId] ?? 'Producto eliminado'),
                // Misma convención que la ruta HTTP: región/artesano aún no existen en schema.
                'region' => $this->regionMock($articuloId),
                'artesano' => $this->artesanoMock($articuloId),
                'precio_unitario' => (float) $fila->precio_unitario,
                'cantidad_vendida' => (int) $fila->cantidad_vendida,
                'total_vendido' => (float) $fila->total_vendido,
            ];
        })->values()->all();
    }

    public function ventasPorRegion(string $categoria): array
    {
        // TODO backend: no hay región ni categoría por prenda en el Articulo del API actual.
        // Sustituir esta función completa cuando exista el dato real.
        $base = [1250, 980, 740, 420, 310, 680, 210, 550];

        if ($categoria === 'Todos') {
            return array_map(fn($v) => (int) round($v * 1.4), $base);
        }

        return $base;
    }

    public function artesanosDestacados(): array
    {
        // TODO backend: no existe endpoint de "Vendedores" en el API actual.
        return [
            ['nombre' => 'Juana V.', 'color' => 'D81B60'],
            ['nombre' => 'Pedro L.', 'color' => '4338CA'],
            ['nombre' => 'María C.', 'color' => '0D9488'],
            ['nombre' => 'Rosa M.', 'color' => 'EA580C'],
        ];
    }

    public function alertasModeracion(): array
    {
        // TODO backend: no existe endpoint de reportes/moderación todavía.
        return [
            [
                'tipo' => 'Publicación Sospechosa',
                'usuario' => '@mariana_oax',
                'motivo' => 'Posible revendedor industrial. Subió un lote de 50 "huipiles estilizados" idénticos que parecen de maquila y no hechos en telar.',
                'fecha' => 'Hace 10 min',
                'urgente' => true,
            ],
            [
                'tipo' => 'Vendedor Sospechoso',
                'usuario' => '@artesanias_premium_mx',
                'motivo' => 'Múltiples usuarios reportan que usa fotos robadas del colectivo de tejedoras de San Juan Cotzocón para vender imitaciones.',
                'fecha' => 'Hace 2 horas',
                'urgente' => false,
            ],
            [
                'tipo' => 'Publicación Sospechosa',
                'usuario' => '@artesano_anonimo',
                'motivo' => 'Denuncia de plagio. Diseños registrados de iconografía sagrada de la Mixteca alta siendo comercializados sin permiso comunitario.',
                'fecha' => 'Ayer',
                'urgente' => false,
            ],
        ];
    }

    private function calcularTopProductos(int $limite): array
    {
        $compras = $this->obtenerColeccion($this->compras->all());

        if (empty($compras)) {
            return [];
        }

        $articulos = $this->obtenerColeccion($this->articulos->all());
        $articulosPorId = collect($articulos)->keyBy('id');

        return collect($compras)
            ->groupBy('articulo_id')
            ->map(function ($grupo, $articuloId) use ($articulosPorId) {
                $articulo = $articulosPorId->get((int) $articuloId);
                $cantidadTotal = (int) $grupo->sum('cantidad');
                $totalVendido = (float) $grupo->sum(fn($c) => $c['cantidad'] * $c['precio_unitario']);
                $precioUnitario = (float) ($grupo->first()['precio_unitario'] ?? ($articulo['precio'] ?? 0));

                return [
                    'id' => (int) $articuloId,
                    'nombre' => $articulo['nombre'] ?? 'Producto eliminado',
                    // TODO backend: región/artesano no existen en el schema Articulo del API.
                    // Reemplazar por $articulo['region'] / $articulo['artesano'] cuando el API los incluya.
                    'region' => $this->regionMock((int) $articuloId),
                    'artesano' => $this->artesanoMock((int) $articuloId),
                    'precio_unitario' => $precioUnitario,
                    'cantidad_vendida' => $cantidadTotal,
                    'total_vendido' => $totalVendido,
                ];
            })
            ->sortByDesc('cantidad_vendida')
            ->take($limite)
            ->values()
            ->toArray();
    }

    private function regionMock(int $articuloId): string
    {
        $regiones = ['Valles', 'Istmo', 'Costa', 'Sierra Sur', 'Sierra Norte', 'Papaloapan', 'Cañada', 'Mixteca'];
        return $regiones[$articuloId % count($regiones)];
    }

    private function artesanoMock(int $articuloId): string
    {
        $artesanos = ['Juana Vásquez', 'Pedro López', 'María Cruz', 'Rosa Martínez', 'Felipe Ramírez'];
        return $artesanos[$articuloId % count($artesanos)];
    }

    private function rangoFechas(string $periodo): array
    {
        return match ($periodo) {
            'semana' => [now()->subDays(7), now()],
            'mes' => [now()->startOfMonth(), now()],
            default => [now()->subYears(5), now()], // 'todo'
        };
    }

    private function rangoAnterior(string $periodo): array
    {
        return match ($periodo) {
            'semana' => [now()->subDays(14), now()->subDays(7)],
            'mes' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            default => [now()->subDays(60), now()->subDays(30)], // tendencia de referencia para 'todo'
        };
    }

    private function filtrarPorFecha(array $items, Carbon $desde, Carbon $hasta): array
    {
        return array_values(array_filter($items, function ($item) use ($desde, $hasta) {
            if (empty($item['created_at'])) {
                return false;
            }
            return Carbon::parse($item['created_at'])->between($desde, $hasta);
        }));
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

    private function obtenerColeccion(Response $response): array
    {
        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();

        // Por si el API pagina la respuesta (data.data) o regresa un array plano
        return $data['data'] ?? $data ?? [];
    }
}
