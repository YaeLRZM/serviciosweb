<?php

use App\Services\Vendedores\VendedoresDataService;
use function Livewire\Volt\{state, computed};

state([
    'error' => null,
    'modalSolicitudes' => false,
]);

$stats = computed(function () {
    try {
        $stats = app(VendedoresDataService::class)->stats();
        $this->error = null;

        return $stats;
    } catch (\Throwable $e) {
        $this->error = 'No se pudieron cargar las estadísticas de vendedores.';

        return [
            'total' => 0,
            'inactivos' => 0,
            'activos' => 0,
        ];
    }
});

$colaVerificacion = computed(function () {
    try {
        return collect(app(VendedoresDataService::class)->solicitudes())
            ->take(3)
            ->values();
    } catch (\Throwable $e) {
        $this->error = 'No se pudieron cargar los vendedores inactivos.';

        return collect();
    }
});

$todasSolicitudes = computed(function () {
    try {
        return collect(app(VendedoresDataService::class)->solicitudes())->values();
    } catch (\Throwable $e) {
        return collect();
    }
});

$abrirSolicitudes = function () {
    $this->modalSolicitudes = true;
};

$cerrarSolicitudes = function () {
    $this->modalSolicitudes = false;
};

$revisarSolicitud = function (int $id) {
    $this->modalSolicitudes = false;
    $this->dispatch('abrirVendedor', id: $id);
};
?>

<div class="space-y-6" x-on:vendedor-actualizado.window="$wire.$refresh()">

    @if ($error)
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-xl font-bold shadow-sm">
        {{ $error }}
    </div>
    @endif

    {{-- Estadísticas reales (activo / inactivo) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-admin.stat-mini-card
            label="Vendedores Totales"
            :value="number_format($this->stats['total'])"
            trend="Registrados en la plataforma"
            trend-color="text-emerald-500"
            icon-bg="bg-[#D81B60]/10"
            icon-color="text-[#D81B60]"
            border-color="border-[#D81B60]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Inactivos"
            :value="$this->stats['inactivos']"
            trend="Estatus inactivo en BD"
            icon-bg="bg-amber-100"
            icon-color="text-amber-500"
            border-color="border-amber-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Activos"
            :value="number_format($this->stats['activos'])"
            trend="Estatus activo en BD"
            icon-bg="bg-emerald-100"
            icon-color="text-emerald-500"
            border-color="border-emerald-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 016.44 3h11.12a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72" />
            </svg>
        </x-admin.stat-mini-card>
    </div>

    {{-- Cola de verificación --}}
    <section>
        <div class="flex items-center justify-between mb-4 gap-3">
            <h3 class="font-cormorant text-2xl text-neutral-900">Vendedores inactivos</h3>
            <button
                type="button"
                wire:click="abrirSolicitudes"
                class="text-sm font-semibold text-[#D81B60] hover:underline shrink-0">
                Ver todos los inactivos
            </button>
        </div>

        @if ($this->colaVerificacion->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-neutral-200 px-5 py-8 text-center">
            <p class="text-sm text-neutral-400">No hay vendedores inactivos.</p>
        </div>
        @else
        <div class="flex gap-4 overflow-x-auto pb-2">
            @foreach ($this->colaVerificacion as $vendedor)
            <x-admin.vendor-queue-card :vendedor="$vendedor" />
            @endforeach
        </div>
        @endif
    </section>

    {{-- Tabla --}}
    <livewire:admin.vendedor.table />

    <livewire:admin.vendedor.form />

    {{-- Modal: todas las solicitudes --}}
    <x-modal :show="$modalSolicitudes" maxWidth="2xl" title="Vendedores inactivos" subtitle="Revisa y activa o desactiva cada registro">
        <x-slot name="closeButton">
            <button type="button" wire:click="cerrarSolicitudes" class="text-neutral-400 hover:text-neutral-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

        @if ($this->todasSolicitudes->isEmpty())
        <div class="py-10 text-center">
            <p class="text-sm text-neutral-400">No hay vendedores inactivos en este momento.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[60vh] overflow-y-auto pr-1">
            @foreach ($this->todasSolicitudes as $solicitud)
            <div class="flex items-center gap-3 p-3 rounded-2xl border border-neutral-100 bg-neutral-50/60 hover:bg-neutral-50 transition">
                <img
                    src="{{ $solicitud['imagen'] }}"
                    alt="{{ $solicitud['tienda'] }}"
                    class="w-14 h-14 rounded-xl object-cover shrink-0 border border-neutral-200" />
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-[#D81B60] truncate">{{ $solicitud['tienda'] }}</h4>
                    <p class="text-xs text-neutral-400 truncate">Prop: {{ $solicitud['propietario'] }}</p>
                    <p class="text-[11px] text-neutral-400 mt-0.5 truncate">{{ $solicitud['email'] }} · {{ $solicitud['ingreso'] }}</p>
                    <p class="text-[11px] text-neutral-500 mt-0.5 font-mono truncate">{{ $solicitud['codigo_ine'] }}</p>
                </div>
                <button
                    type="button"
                    wire:click="revisarSolicitud({{ (int) $solicitud['id'] }})"
                    class="shrink-0 bg-[#D81B60] text-white text-xs font-semibold px-3.5 py-2 rounded-full hover:bg-[#b0124a] transition">
                    Revisar
                </button>
            </div>
            @endforeach
        </div>
        @endif

        <div class="flex justify-end pt-4 mt-2 border-t border-neutral-100">
            <button
                type="button"
                wire:click="cerrarSolicitudes"
                class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
                Cerrar
            </button>
        </div>
    </x-modal>
</div>
