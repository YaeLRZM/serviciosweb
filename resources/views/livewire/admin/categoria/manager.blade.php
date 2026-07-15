<?php

use App\Services\Categorias\CategoriasDataService;
use function Livewire\Volt\{computed};

$categorias = computed(fn () => app(CategoriasDataService::class)->listar());

$stats = computed(function () {
    $items = collect($this->categorias);

    return [
        'total' => $items->count(),
        'visibles' => $items->where('visible', true)->count(),
        'ocultas' => $items->where('visible', false)->count(),
        'nuevas' => $items->filter(fn ($c) => \Illuminate\Support\Carbon::parse($c['created_at'])->gte(now()->startOfMonth()))->count(),
    ];
});

$alternarVisibilidad = function ($id) {
    app(CategoriasDataService::class)->alternarVisibilidad($id);
    session()->flash('mensaje', 'Visibilidad actualizada.');
};

$eliminar = function ($id) {
    app(CategoriasDataService::class)->eliminar($id);
    session()->flash('mensaje', 'Categoría eliminada.');
};
?>

<div class="space-y-6" x-on:categoria-guardada.window="$wire.$refresh()">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="font-cormorant text-3xl text-neutral-900">Gestión de categorías</h1>
            <p class="font-dm-sans text-sm text-neutral-500 mt-1">Organiza el catálogo de artesanías por categoría.</p>
        </div>

        <button
            wire:click="$dispatch('crearCategoria')"
            class="flex items-center gap-2 text-sm font-semibold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-full shadow-md transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nueva categoría
        </button>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-admin.stat-mini-card
            label="Total de categorías"
            :value="$this->stats['total']"
            trend="En el catálogo"
            icon-bg="bg-[#D81B60]/10"
            icon-color="text-[#D81B60]"
            border-color="border-[#D81B60]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Visibles"
            :value="$this->stats['visibles']"
            trend="Publicadas en el catálogo"
            trend-color="text-emerald-600"
            icon-bg="bg-emerald-100"
            icon-color="text-emerald-600"
            border-color="border-emerald-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Ocultas"
            :value="$this->stats['ocultas']"
            trend="Fuera del catálogo"
            trend-color="text-rose-500"
            icon-bg="bg-rose-100"
            icon-color="text-rose-500"
            border-color="border-rose-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Nuevas este mes"
            :value="$this->stats['nuevas']"
            trend="Agregadas al catálogo"
            trend-color="text-amber-600"
            icon-bg="bg-amber-100"
            icon-color="text-amber-600"
            border-color="border-amber-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </x-admin.stat-mini-card>
    </div>

    {{-- Grid de categorías --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($this->categorias as $categoria)
        <x-admin.categoria.category-card :categoria="$categoria" />
        @empty
        <p class="text-sm text-neutral-400 col-span-full text-center py-10">Aún no hay categorías. Crea la primera con el botón de arriba.</p>
        @endforelse

        {{-- Tarjeta "Nueva categoría" --}}
        <button
            wire:click="$dispatch('crearCategoria')"
            class="rounded-2xl border-2 border-dashed border-neutral-300 flex flex-col items-center justify-center text-center p-8 hover:border-[#D81B60] hover:bg-[#D81B60]/5 transition min-h-[220px]">
            <div class="w-12 h-12 rounded-full bg-[#D81B60]/10 text-[#D81B60] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </div>
            <div class="font-cormorant text-xl text-neutral-900">Nueva categoría</div>
            <p class="text-xs text-neutral-500 mt-2 leading-relaxed max-w-[200px]">
                Agrega un nuevo segmento al catálogo de artesanías.
            </p>
        </button>
    </div>

    <livewire:admin.categoria.form />
</div>
