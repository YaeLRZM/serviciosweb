<?php

use App\Services\Api\DashboardApiService;
use Livewire\Volt\Component;

new class extends Component {
    public string $categoria_filtro = 'Textiles';
    public array $datosRegion = [];
    public bool $cargando = true;
    public ?string $error = null;

    private array $paleta = ['#E65C00', '#D81B60', '#2E8B57', '#EAB308', '#990000', '#008080', '#7C3AED', '#0EA5E9'];

    public function mount(): void
    {
        $this->cargarVentas();
    }

    public function updatedCategoriaFiltro(): void
    {
        $this->cargarVentas();
    }

    private bool $usarMock = true;

    public function cargarVentas(): void
    {
        $this->cargando = true;
        $this->error = null;

        if ($this->usarMock) {

            if ($this->categoria_filtro === 'Textiles') {

                $this->datosRegion = [
                    [
                        'region' => 'Valles Centrales',
                        'ventas' => 420,
                        'top_prenda' => 'Huipil Bordado',
                    ],
                    [
                        'region' => 'Istmo',
                        'ventas' => 360,
                        'top_prenda' => 'Traje de Tehuana',
                    ],
                    [
                        'region' => 'Mixteca',
                        'ventas' => 280,
                        'top_prenda' => 'Rebozo Artesanal',
                    ],
                    [
                        'region' => 'Sierra Norte',
                        'ventas' => 210,
                        'top_prenda' => 'Blusa de Lana',
                    ],
                    [
                        'region' => 'Costa',
                        'ventas' => 180,
                        'top_prenda' => 'Guayabera Artesanal',
                    ],
                    [
                        'region' => 'Papaloapan',
                        'ventas' => 160,
                        'top_prenda' => 'Camisa Bordada',
                    ],
                    [
                        'region' => 'Sierra Sur',
                        'ventas' => 145,
                        'top_prenda' => 'Jorongo',
                    ],
                    [
                        'region' => 'Cañada',
                        'ventas' => 110,
                        'top_prenda' => 'Faja Tradicional',
                    ],
                ];
            } else {

                $this->datosRegion = [
                    [
                        'region' => 'Valles Centrales',
                        'ventas' => 780,
                        'top_prenda' => 'Huipil Bordado',
                    ],
                    [
                        'region' => 'Istmo',
                        'ventas' => 690,
                        'top_prenda' => 'Traje de Tehuana',
                    ],
                    [
                        'region' => 'Mixteca',
                        'ventas' => 520,
                        'top_prenda' => 'Rebozo Artesanal',
                    ],
                    [
                        'region' => 'Costa',
                        'ventas' => 470,
                        'top_prenda' => 'Sombrero de Palma',
                    ],
                    [
                        'region' => 'Sierra Norte',
                        'ventas' => 430,
                        'top_prenda' => 'Blusa de Lana',
                    ],
                    [
                        'region' => 'Papaloapan',
                        'ventas' => 390,
                        'top_prenda' => 'Collar Artesanal',
                    ],
                    [
                        'region' => 'Sierra Sur',
                        'ventas' => 340,
                        'top_prenda' => 'Jorongo',
                    ],
                    [
                        'region' => 'Cañada',
                        'ventas' => 260,
                        'top_prenda' => 'Bolso Bordado',
                    ],
                ];
            }
        } else {

            $respuesta = app(DashboardApiService::class)
                ->ventasPorRegion($this->categoria_filtro);

            if ($respuesta->successful()) {
                $this->datosRegion = $respuesta->json('data', []);
            } else {
                $this->error = 'No se pudieron cargar las ventas por región.';
                $this->datosRegion = [];
            }
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
    <div class="flex justify-between items-start mb-6">
        <div>
            <h3 class="text-xl font-bold text-[#D81B60]">Ventas por Región (Oaxaca)</h3>
            <p class="text-sm text-gray-500 mt-1">Regiones con mayor impacto cultural y comercial</p>
        </div>

        <div class="relative">
            <select wire:model.live="categoria_filtro"
                class="appearance-none bg-gray-50 border-none text-sm font-medium text-gray-600 rounded-lg focus:ring-2 focus:ring-[#D81B60] py-2 pl-4 pr-9 cursor-pointer">
                <option value="Textiles">Solo Textiles</option>
                <option value="Todos">Todo el Catálogo</option>
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
            <div class="flex-1 bg-gray-100 rounded-t-lg animate-pulse" style="height: {{ rand(30, 90) }}%">
    </div>
    @endfor
</div>
@elseif (empty($datosRegion))
<div class="h-72 flex items-center justify-center">
    <p class="text-sm text-gray-400">No hay datos de ventas por región disponibles.</p>
</div>
@else
@php $maxVentas = collect($datosRegion)->max('ventas') ?: 1; @endphp

<div class="h-72 flex items-end gap-3 pt-4 border-b border-l border-gray-100 px-4">
    @foreach($datosRegion as $index => $item)
    @php $alturaPorcentaje = ($item['ventas'] / $maxVentas) * 100; @endphp
    <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
        <div class="absolute bottom-full mb-2 bg-[#2B2B2B] text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none text-center shadow-lg z-10 w-48">
            <p class="font-bold border-b border-gray-700 pb-1 mb-1">{{ $item['region'] }}</p>
            <p class="text-gray-300 mb-1">{{ $item['ventas'] }} prendas vendidas</p>
            @if (!empty($item['top_prenda']))
            <p class="text-[10px] text-[#F8F5F2] italic">Top: {{ $item['top_prenda'] }}</p>
            @endif
        </div>

        <div style="height: {{ $alturaPorcentaje }}%; background-color: {{ $this->getColor($index) }};"
            class="w-full opacity-90 group-hover:opacity-100 group-hover:scale-x-105 rounded-t-lg transition-all duration-300 cursor-pointer">
        </div>
    </div>
    @endforeach
</div>

<div class="flex gap-3 mt-3 px-4">
    @foreach($datosRegion as $item)
    <div class="flex-1 text-center text-[11px] font-bold text-gray-400 truncate" title="{{ $item['region'] }}">
        {{ $item['region'] }}
    </div>
    @endforeach
</div>
@endif
</div>