<?php

use function Livewire\Volt\{state, computed, on};

state(['modalAbierto' => false]);

// TODO: reemplazar por Categoria::query()->withCount('productos')->get()
$categorias = computed(fn() => [
    ['id' => 1, 'nombre' => 'Textiles', 'imagen' => 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=800', 'productos' => 342, 'artesanos_extra' => 12, 'visible' => true],
    ['id' => 2, 'nombre' => 'Ceramics', 'imagen' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=800', 'productos' => 218, 'artesanos_extra' => 8, 'visible' => true],
    ['id' => 3, 'nombre' => 'Wood Carvings', 'imagen' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=800', 'productos' => 156, 'artesanos_extra' => 5, 'visible' => true],
    ['id' => 4, 'nombre' => 'Palm Weaving', 'imagen' => 'https://images.unsplash.com/photo-1595408076683-577a0e414ed3?w=800', 'productos' => 48, 'artesanos_extra' => 2, 'visible' => false],
]);

$stats = computed(function () {
    $items = collect($this->categorias);
    return [
        'total' => $items->count(),
        'productos' => $items->sum('productos'),
        'artesanos' => 54, // TODO: Artesano::where('activo', true)->count()
        'salud' => 98,     // TODO: métrica real de calidad de catálogo
    ];
});

on(['categoria-guardada' => function () {
    $this->modalAbierto = false;
    // TODO: cuando haya backend, aquí no hace falta nada extra si $categorias
    // es un computed que consulta la BD — se refresca solo.
}]);

on(['cerrar-form-categoria' => function () {
    $this->modalAbierto = false;
}]);

$abrirCrear = function () {
    $this->modalAbierto = true;
};

$alternarVisibilidad = function ($id) {
    // TODO: Categoria::find($id)->update(['visible' => !$categoria->visible]);
    session()->flash('mensaje', 'Visibilidad actualizada.');
};

$restaurar = function ($id) {
    // TODO: Categoria::find($id)->update(['visible' => true]);
    session()->flash('mensaje', 'Categoría restaurada al catálogo.');
};

$eliminar = function ($id) {
    // TODO: Categoria::find($id)->delete();
    session()->flash('mensaje', 'Categoría eliminada.');
};
?>

<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="font-cormorant text-3xl text-neutral-900">Category Management</h1>
            <p class="font-dm-sans text-sm text-neutral-500 mt-1">Curate and organize the soul of Oaxacan craft heritage.</p>
        </div>

        <button
            wire:click="abrirCrear"
            class="flex items-center gap-2 text-sm font-semibold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-full shadow-md transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add New Category
        </button>
    </div>

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-admin.stat-mini-card
            label="Total Categories"
            :value="$this->stats['total']"
            trend="+2 this month"
            trend-color="text-emerald-500"
            icon-bg="bg-[#D81B60]/10"
            icon-color="text-[#D81B60]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Live Products"
            :value="$this->stats['productos']"
            trend="Across all categories">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Active Artisans"
            :value="$this->stats['artesanos']"
            trend="Crafting now">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Catalog Health"
            :value="$this->stats['salud'] . '%'"
            trend="Optimal"
            trend-color="text-emerald-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </x-admin.stat-mini-card>
    </div>

    {{-- Grid de categorías --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($this->categorias as $categoria)
        <x-admin.categoria.category-card :categoria="$categoria" />
        @endforeach

        {{-- Tarjeta "Nueva categoría" --}}
        <button
            wire:click="abrirCrear"
            class="rounded-2xl border-2 border-dashed border-neutral-300 flex flex-col items-center justify-center text-center p-8 hover:border-[#D81B60] hover:bg-[#D81B60]/5 transition min-h-[280px]">
            <div class="w-12 h-12 rounded-full bg-[#D81B60]/10 text-[#D81B60] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </div>
            <div class="font-cormorant text-xl text-neutral-900">New Category</div>
            <p class="text-xs text-neutral-500 mt-2 leading-relaxed max-w-[200px]">
                Expand the artisanal collection with a new curated segment.
            </p>
        </button>
    </div>

    {{-- Modal de alta rápida --}}
    <x-modal :show="$modalAbierto" title="Nueva categoría" subtitle="Agrega una nueva categoría al catálogo">
        <x-slot name="closeButton">
            <button wire:click="$set('modalAbierto', false)" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

        @if ($modalAbierto)
        <livewire:admin.categoria.form :key="'crear-categoria'" />
        @endif
    </x-modal>
</div>