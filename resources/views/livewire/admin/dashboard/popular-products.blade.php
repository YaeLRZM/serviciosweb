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
            $this->productos = app(DashboardStatsService::class)->productosPopularesDesdeBd(3);
        } catch (\Throwable $e) {
            $this->productos = [];
            $this->error = 'No se pudieron cargar las prendas populares.';
        } finally {
            $this->cargando = false;
        }
    }

    public function exportarReporte()
    {
        try {
            $productos = app(DashboardStatsService::class)->topProductosVendidosDesdeBd(20);
        } catch (\Throwable $e) {
            $productos = [];
        }

        if (empty($productos)) {
            session()->flash('info', 'Aún no se han registrado ventas en detalle_ventas.');

            return;
        }

        $totalGeneral = collect($productos)->sum('total_vendido');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reportes.top-productos-pdf', [
            'productos' => $productos,
            'totalGeneral' => $totalGeneral,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'top-20-prendas-vendidas-' . now()->format('Ymd-His') . '.pdf'
        );
    }
};
?>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6 flex flex-col h-[430px]">
    <h3 class="text-lg font-bold text-[#D81B60] mb-4 flex-shrink-0">Prendas populares</h3>

    @if (session('info'))
    <div class="bg-blue-50 text-blue-600 text-xs rounded-xl p-3 mb-3 flex-shrink-0">{{ session('info') }}</div>
    @endif

    @if ($error)
    <div class="bg-red-50 text-red-600 text-xs rounded-xl p-3 mb-3 flex-shrink-0">{{ $error }}</div>
    @endif

    <div class="space-y-2 flex-1 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200">
        @if ($cargando)
        @for ($i = 0; $i < 3; $i++)
            <div class="h-14 bg-gray-100 rounded-xl animate-pulse mb-2"></div>
        @endfor
        @elseif (empty($productos))
        <div class="h-full flex items-center justify-center text-center">
            <p class="text-sm text-gray-400">Aún no hay líneas en detalle_ventas.</p>
        </div>
        @else
        @foreach ($productos as $producto)
        <div class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 transition">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 bg-[#F2F7F9] rounded-xl overflow-hidden flex items-center justify-center text-[#D81B60] font-bold shrink-0">
                    {{ mb_strtoupper(mb_substr($producto['nombre'], 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-800 truncate">{{ $producto['nombre'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $producto['region'] }} · {{ $producto['artesano'] }}</p>
                </div>
            </div>
            <div class="text-right shrink-0">
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
        <span wire:loading.remove wire:target="exportarReporte">Exportar top 20 (PDF)</span>
        <span wire:loading wire:target="exportarReporte">Generando PDF...</span>
    </button>
</div>
