<?php

use App\Services\Publicaciones\PublicacionesDataService;
use function Livewire\Volt\{state, computed};

state([
    'error' => null,
]);

$stats = computed(function () {
    try {
        $items = collect(app(PublicacionesDataService::class)->reportadas());
        $this->error = null;
    } catch (\Throwable $e) {
        $this->error = 'No se pudieron cargar las estadísticas de publicaciones.';
        $items = collect();
    }

    return [
        'reportadas'    => $items->count(),
        'revisadas'     => $items->where('estado', 'REVISADO')->count(),
        'dadas_de_baja' => $items->where('estado', 'ELIMINADO')->count(),
    ];
});
?>

<div class="space-y-6" x-on:publicacion-actualizada.window="$wire.$refresh()">

    @if ($error)
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-xl font-bold shadow-sm">
        {{ $error }}
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-3xl border border-neutral-100 shadow-sm p-5 flex items-start justify-between">
            <div>
                <div class="text-sm text-neutral-400">Publicaciones reportadas</div>
                <div class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['reportadas'] }}</div>
                <div class="text-xs text-rose-500 font-medium mt-1">Requieren atención</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-100 flex items-center justify-center text-rose-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-neutral-100 shadow-sm p-5 flex items-start justify-between">
            <div>
                <div class="text-sm text-neutral-400">Aprobaron el dictamen</div>
                <div class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['revisadas'] }}</div>
                <div class="text-xs text-emerald-500 font-medium mt-1">Sin motivo para bajarlas</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-neutral-100 shadow-sm p-5 flex items-start justify-between">
            <div>
                <div class="text-sm text-neutral-400">Dadas de baja</div>
                <div class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['dadas_de_baja'] }}</div>
                <div class="text-xs text-neutral-500 font-medium mt-1">Retiradas del catálogo</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </div>
    </div>

    <livewire:admin.publicacion.table />

    <livewire:admin.publicacion.form />
</div>