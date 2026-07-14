<?php

use App\Services\Api\DashboardApiService;
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

    private bool $usarMock = true;

    public function cargarDatos(): void
    {
        $this->cargando = true;
        $this->error = null;
        $this->errorVendedores = null;

        if ($this->usarMock) {

            $this->estadisticas = match ($this->filtro_tiempo) {

                'mes' => [
                    'clientes_activos' => 1874,
                    'clientes_crecimiento' => '+12% (vs mes anterior)',
                    'ventas' => 4962,
                    'ventas_crecimiento' => '+8% (vs mes anterior)',
                ],

                'semana' => [
                    'clientes_activos' => 634,
                    'clientes_crecimiento' => '+5% (vs semana anterior)',
                    'ventas' => 1897,
                    'ventas_crecimiento' => '+3% (vs semana anterior)',
                ],

                default => [
                    'clientes_activos' => 3280,
                    'clientes_crecimiento' => '+25% (vs año anterior)',
                    'ventas' => 12450,
                    'ventas_crecimiento' => '+18% (vs año anterior)',
                ],
            };

            $this->vendedores = [
                [
                    'nombre' => 'Ana Lucía',
                    'foto_url' => 'https://ui-avatars.com/api/?name=Ana+Lucia&background=D81B60&color=fff&rounded=true',
                ],
                [
                    'nombre' => 'Miguel Ángel',
                    'foto_url' => 'https://ui-avatars.com/api/?name=Miguel+Angel&background=D81B60&color=fff&rounded=true',
                ],
                [
                    'nombre' => 'Sofia García',
                    'foto_url' => 'https://ui-avatars.com/api/?name=Sofia+Garcia&background=D81B60&color=fff&rounded=true',
                ],
                [
                    'nombre' => 'Carlos Ruiz',
                    'foto_url' => 'https://ui-avatars.com/api/?name=Carlos+Ruiz&background=D81B60&color=fff&rounded=true',
                ],
            ];
        } else {
            $this->realizarPeticionesApi();
        }

        $this->cargando = false;
    }

    private function realizarPeticionesApi(): void
    {
        try {
            $this->estadisticas = app(DashboardStatsService::class)->resumenGeneral($this->filtro_tiempo);
        } catch (\Throwable $e) {
            $this->error = 'No se pudieron cargar las estadísticas.';
        }

        try {
            $respuesta = app(DashboardApiService::class)->vendedoresDestacados();
        } catch (\Throwable $e) {
            $this->errorVendedores = 'No se pudieron cargar los vendedores destacados.';
            $this->vendedores = [];
        }

        if ($respuesta->successful()) {
            $this->vendedores = $respuesta->json('data', []);
        } else {
            $this->errorVendedores = 'No se pudieron cargar los vendedores destacados.';
            $this->vendedores = [];
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
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Clientes Activos</p>
            @if ($cargando)
            <div class="h-10 w-24 bg-gray-200 rounded animate-pulse"></div>
            @else
            <div class="flex items-baseline gap-3">
                <p class="text-5xl font-extrabold text-[#D81B60]">{{ number_format($estadisticas['clientes_activos'] ?? 0) }}</p>
                <span class="text-emerald-500 text-sm font-bold">{{ $estadisticas['clientes_crecimiento'] ?? '—' }}</span>
            </div>
            @endif
        </div>

        <div class="bg-[#F8F5F2] rounded-3xl p-6 sm:p-8">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ventas Realizadas</p>
            @if ($cargando)
            <div class="h-10 w-24 bg-gray-200 rounded animate-pulse"></div>
            @else
            <div class="flex items-baseline gap-3">
                <p class="text-5xl font-extrabold text-[#D81B60]">{{ number_format($estadisticas['ventas'] ?? 0) }}</p>
                <span class="text-emerald-500 text-sm font-bold">{{ $estadisticas['ventas_crecimiento'] ?? '—' }}</span>
            </div>
            @endif
        </div>
    </div>

    <div>
        <h4 class="text-sm font-bold text-gray-700 mb-5">Vendedores destacados</h4>

        @if ($errorVendedores)
        <div class="bg-red-50 text-red-600 text-xs rounded-xl p-3">{{ $errorVendedores }}</div>
        @elseif ($cargando)
        <div class="flex gap-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="h-11 w-32 bg-gray-100 rounded-full animate-pulse">
        </div>
        @endfor
    </div>
    @elseif (empty($vendedores))
    <p class="text-sm text-gray-400">Aún no hay vendedores destacados.</p>
    @else
    <div class="flex flex-wrap gap-4">
        @foreach ($vendedores as $vendedor)
        <div class="flex items-center gap-5 bg-white border border-gray-100 rounded-full pr-6 py-1.5 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
            <img src="{{ $vendedor['foto_url'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($vendedor['nombre']) . '&background=D81B60&color=fff&rounded=true') }}"
                alt="{{ $vendedor['nombre'] }}" class="w-10 h-10 rounded-full object-cover">
            <span class="text-sm font-bold text-gray-700">{{ $vendedor['nombre'] }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
</div>