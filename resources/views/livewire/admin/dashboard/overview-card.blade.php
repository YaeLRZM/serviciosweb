<?php

use App\Services\Dashboard\DashboardStatsService;
use Livewire\Volt\Component;

new class extends Component {
    public string $filtro_tiempo = 'todo';
    public array $estadisticas = [];
    public array $vendedores = [];
    public bool $cargando = true;
    public ?string $error = null;
    public ?string $errorVendedores = null;

    public function mount(): void
    {
        $this->cargarDatos();
    }

    public function updatedFiltroTiempo(): void
    {
        $this->cargarDatos();
    }

    public function cargarDatos(): void
    {
        $this->cargando = true;
        $this->error = null;
        $this->errorVendedores = null;

        try {
            $svc = app(DashboardStatsService::class);
            $this->estadisticas = $svc->resumenGeneral($this->filtro_tiempo);
            $this->vendedores = $svc->vendedoresDestacados(4);
        } catch (\Throwable $e) {
            $this->error = 'No se pudieron cargar las estadísticas del dashboard.';
            $this->estadisticas = [
                'clientes_activos' => 0,
                'clientes_crecimiento' => '—',
                'ventas' => 0,
                'ventas_crecimiento' => '—',
            ];
            $this->vendedores = [];
            $this->errorVendedores = 'No se pudieron cargar los vendedores destacados.';
        }

        $this->cargando = false;
    }
};
?>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6 md:p-8">
    <div class="flex justify-between items-center mb-8">
        <h3 class="text-xl font-bold text-[#2B2B2B]">Vista General</h3>

        <div class="relative">
            <select wire:model.live="filtro_tiempo"
                class="appearance-none bg-gray-50 border-none text-sm text-gray-600 rounded-lg focus:ring-2 focus:ring-[#D81B60] py-2 pl-4 pr-9 cursor-pointer">
                <option value="todo">Todo el tiempo</option>
                <option value="mes">Este mes</option>
                <option value="semana">Esta semana</option>
            </select>
            <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    @if ($error)
    <div class="bg-red-50 text-red-600 text-sm rounded-xl p-4 mb-6">{{ $error }}</div>
    @endif

    <div wire:loading.delay wire:target="filtro_tiempo" class="text-xs text-gray-400 mb-4">Actualizando...</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
        <div class="bg-[#F8F5F2] rounded-3xl p-6 sm:p-8">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Clientes con compra</p>
            @if ($cargando)
            <div class="h-10 w-24 bg-gray-200 rounded animate-pulse"></div>
            @else
            <div class="flex items-baseline gap-3">
                <p class="text-5xl font-extrabold text-[#D81B60]">{{ number_format($estadisticas['clientes_activos'] ?? 0) }}</p>
                <span class="text-emerald-500 text-sm font-bold">{{ $estadisticas['clientes_crecimiento'] ?? '—' }}</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Users distintos con venta completada</p>
            @endif
        </div>

        <div class="bg-[#F8F5F2] rounded-3xl p-6 sm:p-8">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ventas completadas</p>
            @if ($cargando)
            <div class="h-10 w-24 bg-gray-200 rounded animate-pulse"></div>
            @else
            <div class="flex items-baseline gap-3">
                <p class="text-5xl font-extrabold text-[#D81B60]">{{ number_format($estadisticas['ventas'] ?? 0) }}</p>
                <span class="text-emerald-500 text-sm font-bold">{{ $estadisticas['ventas_crecimiento'] ?? '—' }}</span>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">estado = completada</p>
            @endif
        </div>
    </div>

    <div>
        <h4 class="text-sm font-bold text-gray-700 mb-5">Vendedores activos (por ventas de tienda)</h4>

        @if ($errorVendedores)
        <div class="bg-red-50 text-red-600 text-xs rounded-xl p-3">{{ $errorVendedores }}</div>
        @elseif ($cargando)
        <div class="flex gap-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="h-11 w-32 bg-gray-100 rounded-full animate-pulse"></div>
            @endfor
        </div>
        @elseif (empty($vendedores))
        <p class="text-sm text-gray-400">No hay vendedores activos.</p>
        @else
        <div class="flex flex-wrap gap-4">
            @foreach ($vendedores as $vendedor)
            <div class="flex items-center gap-3 bg-white border border-gray-100 rounded-full pr-5 py-1.5 shadow-sm hover:shadow-md transition-shadow">
                <img src="{{ $vendedor['foto_url'] }}"
                    alt="{{ $vendedor['nombre'] }}" class="w-10 h-10 rounded-full object-cover">
                <div class="min-w-0">
                    <span class="text-sm font-bold text-gray-700 block truncate max-w-[140px]">{{ $vendedor['nombre'] }}</span>
                    <span class="text-[10px] text-gray-400 block truncate max-w-[140px]">{{ $vendedor['tienda'] ?? '' }} · {{ $vendedor['ventas'] ?? 0 }} ventas</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
