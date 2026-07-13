<?php

use function Livewire\Volt\{state, mount};

state([
    'categoriaId' => null,
    'nombre' => '',
    'descripcion' => '',
    'imagen' => '',
    'visible' => true,
]);

mount(function ($categoriaId = null) {
    $this->categoriaId = $categoriaId;

    if ($categoriaId) {
        // TODO: reemplazar por Categoria::findOrFail($categoriaId)
        $mock = [
            1 => ['nombre' => 'Textiles', 'descripcion' => 'Tejidos y bordados tradicionales oaxaqueños.', 'imagen' => 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=800', 'visible' => true],
            2 => ['nombre' => 'Ceramics', 'descripcion' => 'Barro negro y piezas de alfarería.', 'imagen' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=800', 'visible' => true],
        ][$categoriaId] ?? null;

        if ($mock) {
            $this->nombre = $mock['nombre'];
            $this->descripcion = $mock['descripcion'];
            $this->imagen = $mock['imagen'];
            $this->visible = $mock['visible'];
        }
    }
});

$rules = [
    'nombre' => 'required|min:3|max:60',
    'descripcion' => 'nullable|max:280',
    'imagen' => 'nullable|url',
];

$guardar = function () {
    $this->validate();

    // TODO: reemplazar por:
    // Categoria::updateOrCreate(['id' => $this->categoriaId], [
    //     'nombre' => $this->nombre,
    //     'descripcion' => $this->descripcion,
    //     'imagen' => $this->imagen,
    //     'visible' => $this->visible,
    // ]);

    $this->dispatch('categoria-guardada');
    session()->flash('mensaje', $this->categoriaId ? 'Categoría actualizada.' : 'Categoría creada.');
};
?>

<div class="space-y-4">
    <div>
        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nombre</label>
        <input
            wire:model="nombre"
            type="text"
            placeholder="Ej. Textiles"
            class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] transition-all" />
        @error('nombre') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Descripción</label>
        <textarea
            wire:model="descripcion"
            rows="3"
            placeholder="Breve descripción de la categoría..."
            class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] transition-all"></textarea>
        @error('descripcion') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Imagen (URL)</label>
        <input
            wire:model="imagen"
            type="text"
            placeholder="https://..."
            class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] transition-all" />
        {{-- TODO: cuando exista backend, cambiar por <input type="file" wire:model="imagenArchivo"> con almacenamiento real --}}
        @error('imagen') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-2.5 cursor-pointer">
        <input wire:model="visible" type="checkbox" class="rounded border-neutral-300 text-[#D81B60] focus:ring-[#D81B60]" />
        <span class="text-sm text-neutral-600">Visible en el catálogo</span>
    </label>

    <div class="flex justify-end gap-2.5 pt-2">
        <button type="button" wire:click="$dispatch('cerrar-form-categoria')" class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
            Cancelar
        </button>
        <button wire:click="guardar" class="text-xs font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-xl shadow-md transition">
            Guardar
        </button>
    </div>
</div>