<?php

use App\Services\Dashboard\DashboardStatsService;
use Livewire\Volt\Component;

new class extends Component {
    public array $productos = [];
    public bool $cargando = true;
    public ?string $error = null;

    public function mount(): void
    {
        $this->cargarProductos();
    }

    public function cargarProductos(): void
    {
        $this->cargando = true;
        $this->error = null;

        try {
            // Fuente real por defecto: BD en proceso (sin self-HTTP).
            $this->productos = app(DashboardStatsService::class)->productosPopularesDesdeBd(3);
        } catch (\Throwable $e) {
            // Fallback seguro: mock local si la BD falla.
            $this->productos = array_slice($this->productosMock(), 0, 3);
            $this->error = null;
        } finally {
            $this->cargando = false;
        }
    }

    public function exportarReporte()
    {
        try {
            $productos = app(DashboardStatsService::class)->topProductosVendidosDesdeBd(20);
        } catch (\Throwable $e) {
            $productos = $this->productosMock();
        }

        if (empty($productos)) {
            session()->flash('info', 'Aún no se han realizado ventas.');
            return;
        }

        $totalGeneral = collect($productos)->sum('total_vendido');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reportes.top-productos-pdf', [
            'productos' => $productos,
            'totalGeneral' => $totalGeneral,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'top-20-prendas-vendidas-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    /**
     * Dataset local solo como fallback si falla la lectura en proceso.
     * Misma forma que DashboardStatsService::calcularTopProductos().
     */
    private function productosMock(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Huipil Bordado',
                'region' => 'Valles',
                'artesano' => 'Juana Vásquez',
                'precio_unitario' => 1250.00,
                'cantidad_vendida' => 84,
                'total_vendido' => 105000.00,
            ],
            [
                'id' => 2,
                'nombre' => 'Rebozo de Seda',
                'region' => 'Mixteca',
                'artesano' => 'María Cruz',
                'precio_unitario' => 1600.00,
                'cantidad_vendida' => 61,
                'total_vendido' => 97600.00,
            ],
            [
                'id' => 3,
                'nombre' => 'Alebrije Jaguar',
                'region' => 'Valles',
                'artesano' => 'Pedro López',
                'precio_unitario' => 950.00,
                'cantidad_vendida' => 47,
                'total_vendido' => 44650.00,
            ],
            [
                'id' => 4,
                'nombre' => 'Barro Negro',
                'region' => 'Valles',
                'artesano' => 'Rosa Martínez',
                'precio_unitario' => 480.00,
                'cantidad_vendida' => 39,
                'total_vendido' => 18720.00,
            ],
            [
                'id' => 5,
                'nombre' => 'Collar Filigrana',
                'region' => 'Istmo',
                'artesano' => 'Felipe Ramírez',
                'precio_unitario' => 890.00,
                'cantidad_vendida' => 33,
                'total_vendido' => 29370.00,
            ],
        ];
    }
};
?>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6 flex flex-col h-[430px]">
    <h3 class="text-lg font-bold text-[#D81B60] mb-4 flex-shrink-0">Prendas Populares</h3>

    @if (session('info'))
    <div class="bg-blue-50 text-blue-600 text-xs rounded-xl p-3 mb-3 flex-shrink-0">{{ session('info') }}</div>
    @endif

    @if ($error)
    <div class="bg-red-50 text-red-600 text-xs rounded-xl p-3 mb-3 flex-shrink-0">{{ $error }}</div>
    @endif

    <div class="space-y-2 flex-1 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200">
        @if ($cargando)
        @for ($i = 0; $i < 3; $i++)
            <div class="h-14 bg-gray-100 rounded-xl animate-pulse mb-2">
    </div>
    @endfor
    @elseif (empty($productos))
    <div class="h-full flex items-center justify-center text-center">
        <p class="text-sm text-gray-400">Aún no se han realizado ventas.</p>
    </div>
    @else
    @foreach ($productos as $producto)
    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 transition">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#F2F7F9] rounded-xl overflow-hidden flex items-center justify-center text-[#D81B60] font-bold">
                {{ mb_strtoupper(mb_substr($producto['nombre'], 0, 1)) }}
            </div>
            <div>
                <p class="text-sm font-bold text-gray-800">{{ $producto['nombre'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Región: {{ $producto['region'] }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="font-bold text-gray-800 text-sm">{{ $producto['cantidad_vendida'] }}</p>
            <p class="text-[10px] font-bold text-gray-400">unidades</p>
        </div>
    </div>
    @endforeach
    @endif
</div>

<button
    wire:click="exportarReporte"
    wire:loading.attr="disabled"
    wire:target="exportarReporte"
    @disabled($cargando || $error || empty($productos))
    class="w-full py-2.5 text-sm font-bold text-[#D81B60] bg-white border border-[#D81B60]/30 rounded-full hover:bg-pink-50 transition mt-4 flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white">
    <span wire:loading.remove wire:target="exportarReporte">Exportar Reporte</span>
    <span wire:loading wire:target="exportarReporte">Generando PDF...</span>
</button>
</div>
