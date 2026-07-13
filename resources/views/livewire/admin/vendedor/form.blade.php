<?php

use function Livewire\Volt\{state, on};

state([
    'isOpen' => false,
    'vendedorId' => null,
    'tienda' => '',
    'propietario' => '',
    'dictamen' => 'Verificado',
    'notas' => '',
]);

on(['abrirVendedor' => function ($id) {
    // TODO: reemplazar por Vendedor::findOrFail($id)
    $mock = [
        1 => ['tienda' => 'Arte en Filigrana', 'propietario' => 'Sofia Mendoza', 'estatus' => 'Verificado'],
        2 => ['tienda' => 'Talabartería Oaxaca', 'propietario' => 'Carlos Vazquez', 'estatus' => 'En Revisión'],
        3 => ['tienda' => 'Sabores del Sur', 'propietario' => 'Ricardo Gomez', 'estatus' => 'Suspendido'],
        4 => ['tienda' => 'Bordados Juchitán', 'propietario' => 'Ximena Morales', 'estatus' => 'Verificado'],
    ][$id] ?? ['tienda' => 'Tienda #' . $id, 'propietario' => '', 'estatus' => 'En Revisión'];

    $this->vendedorId = $id;
    $this->tienda = $mock['tienda'];
    $this->propietario = $mock['propietario'];
    $this->dictamen = $mock['estatus'];
    $this->notas = '';
    $this->isOpen = true;
}]);

$guardar = function () {
    // TODO: Vendedor::findOrFail($this->vendedorId)->update([
    //     'estatus' => $this->dictamen,
    //     'notas_moderacion' => $this->notas,
    // ]);

    $this->isOpen = false;
    $this->dispatch('vendedor-actualizado');
    session()->flash('mensaje', 'Estatus de la tienda actualizado.');
};
?>

<div>
    <x-modal :show="$isOpen" :title="$tienda" :subtitle="'Propietario: ' . $propietario">
        <x-slot name="closeButton">
            <button wire:click="$set('isOpen', false)" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

        <div class="space-y-5">
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Dictamen</label>
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" wire:click="$set('dictamen', 'Verificado')"
                        class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $dictamen === 'Verificado' ? 'border-green-500 bg-green-50 text-green-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span class="text-xs">Verificar</span>
                    </button>

                    <button type="button" wire:click="$set('dictamen', 'En Revisión')"
                        class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $dictamen === 'En Revisión' ? 'border-amber-500 bg-amber-50 text-amber-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs">En revisión</span>
                    </button>

                    <button type="button" wire:click="$set('dictamen', 'Suspendido')"
                        class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $dictamen === 'Suspendido' ? 'border-rose-500 bg-rose-50 text-rose-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM6 6l12 12" />
                        </svg>
                        <span class="text-xs">Suspender</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Notas internas</label>
                <textarea wire:model="notas" rows="3" placeholder="Escribe el motivo del dictamen..."
                    class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] placeholder-gray-400 transition-all"></textarea>
            </div>

            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" wire:click="$set('isOpen', false)" class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">Cerrar</button>
                <button wire:click="guardar" class="text-xs font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-xl shadow-md transition">Aplicar</button>
            </div>
        </div>
    </x-modal>
</div>