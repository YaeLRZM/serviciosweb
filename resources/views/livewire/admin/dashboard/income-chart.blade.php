<?php

use App\Services\Dashboard\DashboardStatsService;
use Livewire\Volt\Component;

new class extends Component {
    /** 'Todos' o id/nombre de categoría real */
    public string $categoria_filtro = 'Todos';
    public array $categorias = [];
    public array $datosRegion = [];
    public bool $cargando = true;
    public ?string $error = null;

    private array $paleta = ['#E65C00', '#D81B60', '#2E8B57', '#EAB308', '#990000', '#008080', '#7C3AED', '#0EA5E9'];

    public function mount(): void
    {
        try {
            $this->categorias = app(DashboardStatsService::class)->categoriasFiltro();
        } catch (\Throwable $e) {
            $this->categorias = [];
        }

        $this->cargarVentas();
    }

    public function updatedCategoriaFiltro(): void
    {
        $this->cargarVentas();
    }

    public function cargarVentas(): void
    {
        $this->cargando = true;
        $this->error = null;

        try {
            $this->datosRegion = app(DashboardStatsService::class)
                ->ventasPorRegion($this->categoria_filtro);
        } catch (\Throwable $e) {
            $this->error = 'No se pudieron cargar las ventas por región.';
            $this->datosRegion = [];
        }

        $this->cargando = false;
    }

    public function getColor(int $index): string
    {
        return $this->paleta[$index % count($this->paleta)];
    }
};
?>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6">
    <div class="flex justify-between items-start mb-6 gap-3">
        <div>
            <h3 class="text-xl font-bold text-[#D81B60]">Ventas por región</h3>
            <p class="text-sm text-gray-500 mt-1">Unidades vendidas agrupadas por <code class="text-xs">articulos.region</code></p>
        </div>

        <div class="relative shrink-0">
            <select wire:model.live="categoria_filtro"
                class="appearance-none bg-gray-50 border-none text-sm font-medium text-gray-600 rounded-lg focus:ring-2 focus:ring-[#D81B60] py-2 pl-4 pr-9 cursor-pointer max-w-[200px]">
                <option value="Todos">Todo el catálogo</option>
                @foreach ($categorias as $cat)
                <option value="{{ $cat['id'] }}">{{ $cat['nombre'] }}</option>
                @endforeach
            </select>
            <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    @if ($error)
    <div class="bg-red-50 text-red-600 text-sm rounded-xl p-4">{{ $error }}</div>
    @elseif ($cargando)
    <div class="h-72 flex items-end gap-3 px-4">
        @for ($i = 0; $i < 8; $i++)
            <div class="flex-1 bg-gray-100 rounded-t-lg animate-pulse" style="height: {{ 30 + ($i * 7) }}%"></div>
        @endfor
    </div>
    @elseif (empty($datosRegion))
    <div class="h-72 flex items-center justify-center">
        <p class="text-sm text-gray-400">No hay datos de ventas por región.</p>
    </div>
    @else
    @php $maxVentas = collect($datosRegion)->max('ventas') ?: 1; @endphp

    <div class="h-72 flex items-end gap-2 pt-4 border-b border-l border-gray-100 px-2 overflow-x-auto">
        @foreach($datosRegion as $index => $item)
        @php $alturaPorcentaje = max(4, ($item['ventas'] / $maxVentas) * 100); @endphp
        <div class="flex-1 min-w-[2.5rem] flex flex-col items-center group relative h-full justify-end">
            <div class="absolute bottom-full mb-2 bg-[#2B2B2B] text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none text-center shadow-lg z-10 w-44">
                <p class="font-bold border-b border-gray-700 pb-1 mb-1 truncate">{{ $item['region'] }}</p>
                <p class="text-gray-300 mb-1">{{ number_format($item['ventas']) }} unidades</p>
                @if (!empty($item['top_prenda']))
                <p class="text-[10px] text-[#F8F5F2] italic truncate">Top: {{ $item['top_prenda'] }}</p>
                @endif
            </div>

            <div style="height: {{ $alturaPorcentaje }}%; background-color: {{ $this->getColor($index) }};"
                class="w-full opacity-90 group-hover:opacity-100 group-hover:scale-x-105 rounded-t-lg transition-all duration-300 cursor-pointer">
            </div>
        </div>
        @endforeach
    </div>

    <div class="flex gap-2 mt-3 px-2 overflow-x-auto">
        @foreach($datosRegion as $item)
        <div class="flex-1 min-w-[2.5rem] text-center text-[10px] font-bold text-gray-400 truncate" title="{{ $item['region'] }}">
            {{ $item['region'] }}
        </div>
        @endforeach
    </div>
    @endif
</div>
