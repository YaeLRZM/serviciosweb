<?php

use App\Services\Categorias\CategoriasDataService;
use function Livewire\Volt\{state, on, rules, usesFileUploads};

usesFileUploads();

state([
    'isOpen' => false,
    'modo' => 'crear', // 'crear' | 'editar'
    'categoriaId' => null,
    'nombre' => '',
    'descripcion' => '',
    'imagen' => '', // ruta/URL ya guardada (para previsualizar en modo editar)
    'imagenArchivo' => null, // archivo temporal subido desde el explorador
    'imagenUrl' => '', // alternativa: pegar una URL
    'visible' => true,
]);

on(['crearCategoria' => function () {
    $this->reset(['categoriaId', 'nombre', 'descripcion', 'imagen', 'imagenArchivo', 'imagenUrl']);
    $this->modo = 'crear';
    $this->visible = true;
    $this->resetErrorBag();
    $this->isOpen = true;
}]);

on(['abrirCategoria' => function ($id) {
    $categoria = app(CategoriasDataService::class)->find($id);

    if (! $categoria) {
        session()->flash('error', 'No se pudo cargar la categoría.');
        return;
    }

    $this->modo = 'editar';
    $this->categoriaId = $id;
    $this->nombre = $categoria['nombre'];
    $this->descripcion = $categoria['descripcion'] ?? '';
    $this->imagen = $categoria['imagen'] ?? '';
    $this->imagenArchivo = null;
    $this->imagenUrl = '';
    $this->visible = (bool) $categoria['visible'];
    $this->resetErrorBag();
    $this->isOpen = true;
}]);

rules([
    'nombre' => ['required', 'string', 'min:3', 'max:60'],
    'descripcion' => ['nullable', 'string', 'max:280'],
    'imagenArchivo' => ['nullable', 'image', 'max:2048'],
    'imagenUrl' => ['nullable', 'url'],
]);

$guardar = function () {
    $this->validate();

    $imagen = $this->imagen;

    if ($this->imagenArchivo) {
        // Disco public → storage/app/public/categorias; URL relativa pública vía public/storage
        $path = $this->imagenArchivo->store('categorias', 'public');
        $imagen = '/storage/' . ltrim($path, '/');
    } elseif ($this->imagenUrl) {
        $imagen = $this->imagenUrl;
    }

    $datos = [
        'nombre' => $this->nombre,
        'descripcion' => $this->descripcion,
        'imagen' => $imagen,
        'visible' => $this->visible,
    ];

    if ($this->modo === 'crear') {
        app(CategoriasDataService::class)->crear($datos);
        session()->flash('mensaje', 'Categoría creada.');
    } else {
        app(CategoriasDataService::class)->actualizar($this->categoriaId, $datos);
        session()->flash('mensaje', 'Categoría actualizada.');
    }

    $this->isOpen = false;
    $this->dispatch('categoria-guardada');
};
?>

<div>
    <x-modal :show="$isOpen" :title="$modo === 'crear' ? 'Nueva categoría' : 'Editar categoría'" :subtitle="$modo === 'crear' ? 'Agrega una nueva categoría al catálogo' : null">
        <x-slot name="closeButton">
            <button wire:click="$set('isOpen', false)" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

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
        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Imagen</label>

        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden flex items-center justify-center shrink-0">
                @if ($imagenArchivo)
                <img src="{{ $imagenArchivo->temporaryUrl() }}" class="w-full h-full object-cover" alt="Vista previa" />
                @elseif ($imagenUrl)
                <img src="{{ $imagenUrl }}" class="w-full h-full object-cover" alt="Vista previa" />
                @elseif ($imagen)
                <img src="{{ $imagen }}" class="w-full h-full object-cover" alt="Vista previa" />
                @else
                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 4.5h18M3.75 4.5v15a.75.75 0 00.75.75h15a.75.75 0 00.75-.75v-15" />
                </svg>
                @endif
            </div>

            <div class="flex-1 space-y-2">
                <input
                    wire:model="imagenArchivo"
                    type="file"
                    accept="image/*"
                    class="w-full text-xs text-neutral-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#D81B60]/10 file:text-[#D81B60] hover:file:bg-[#D81B60]/20 cursor-pointer" />
                <div wire:loading wire:target="imagenArchivo" class="text-[11px] text-neutral-400">Subiendo imagen...</div>
                @error('imagenArchivo') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror

                <input
                    wire:model="imagenUrl"
                    type="text"
                    placeholder="...o pega el enlace de una imagen"
                    class="w-full bg-gray-50 border border-gray-100 rounded-lg text-xs p-2 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] transition-all" />
                @error('imagenUrl') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <label class="flex items-center gap-2.5 cursor-pointer">
        <input wire:model="visible" type="checkbox" class="rounded border-neutral-300 text-[#D81B60] focus:ring-[#D81B60]" />
        <span class="text-sm text-neutral-600">Visible en el catálogo</span>
    </label>

    <div class="flex justify-end gap-2.5 pt-2">
        <button type="button" wire:click="$set('isOpen', false)" class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
            Cancelar
        </button>
        <button wire:click="guardar" class="text-xs font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-xl shadow-md transition">
            Guardar
        </button>
    </div>
    </div>
    </x-modal>
</div>
