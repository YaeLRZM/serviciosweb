<?php

use function Livewire\Volt\{computed};

// Mismo dataset que table.blade.php — TODO: cuando exista el modelo, ambos deben
// consultar la misma fuente (ej. un scope en el modelo Publicacion) para no duplicar.
$dataset = [
    ['id' => 1,  'estado' => 'Pendiente'],
    ['id' => 2,  'estado' => 'Revisión'],
    ['id' => 3,  'estado' => 'Pendiente'],
    ['id' => 4,  'estado' => 'Aprobado'],
    ['id' => 5,  'estado' => 'Pendiente'],
    ['id' => 6,  'estado' => 'Rechazado'],
    ['id' => 7,  'estado' => 'Revisión'],
    ['id' => 8,  'estado' => 'Aprobado'],
    ['id' => 9,  'estado' => 'Pendiente'],
    ['id' => 10, 'estado' => 'Aprobado'],
    ['id' => 11, 'estado' => 'Pendiente'],
    ['id' => 12, 'estado' => 'Revisión'],
];

$stats = computed(function () use ($dataset) {
    $items = collect($dataset);
    return [
        'pendientes' => $items->where('estado', 'Pendiente')->count(),
        'aprobados'  => $items->where('estado', 'Aprobado')->count(), // TODO: filtrar por hoy cuando haya fechas reales
        'revision'   => $items->where('estado', 'Revisión')->count(),
    ];
});
?>

<div class="space-y-6" x-on:publicacion-actualizada.window="$wire.$refresh()">

    {{-- Tarjetas de estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-3xl border border-neutral-100 shadow-sm p-5 flex items-start justify-between">
            <div>
                <div class="text-sm text-neutral-400">Pendientes</div>
                <div class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['pendientes'] }}</div>
                <div class="text-xs text-rose-500 font-medium mt-1">⚠ Requieren atención</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-100 flex items-center justify-center text-rose-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-neutral-100 shadow-sm p-5 flex items-start justify-between">
            <div>
                <div class="text-sm text-neutral-400">Aprobados</div>
                <div class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['aprobados'] }}</div>
                <div class="text-xs text-emerald-500 font-medium mt-1">↗ Catálogo activo</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-neutral-100 shadow-sm p-5 flex items-start justify-between">
            <div>
                <div class="text-sm text-neutral-400">Revisiones</div>
                <div class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['revision'] }}</div>
                <div class="text-xs text-amber-500 font-medium mt-1">✎ Necesitan feedback</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <livewire:admin.publicacion.table />

    <livewire:admin.publicacion.form />
</div>