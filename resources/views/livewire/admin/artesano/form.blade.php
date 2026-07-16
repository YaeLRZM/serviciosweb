<?php

use App\Services\Artesanos\ArtesanosDataService;
use function Livewire\Volt\{state, on};

state([
    'isOpen' => false,
    'artesanoId' => null,
    'nombreArtesano' => '',
    'especialidadArtesano' => '',
    'dictamen' => 'Aprobar',
    'notas' => '',
]);

on(['abrirRevisionArtesano' => function ($id) {
    $artesano = app(ArtesanosDataService::class)->find($id);

    if (! $artesano) {
        session()->flash('error', 'No se pudo cargar el artesano.');
        return;
    }

    $this->artesanoId = $id;
    $this->nombreArtesano = $artesano['nombre'];
    $this->especialidadArtesano = $artesano['especialidad'];
    $this->dictamen = 'Aprobar';
    $this->notas = '';
    $this->isOpen = true;
}]);

$guardarDictamen = function () {
    app(ArtesanosDataService::class)->guardarDictamen($this->artesanoId, $this->dictamen, $this->notas);

    $this->isOpen = false;
    $this->dispatch('artesano-actualizado');
    session()->flash('mensaje', 'Dictamen de verificación guardado.');
};
?>

<div>
    <x-modal :show="$isOpen" title="Revisión de solicitud" :subtitle="'Solicitud de ' . $nombreArtesano . ($especialidadArtesano ? ' — ' . $especialidadArtesano : '')">
        <x-slot name="closeButton">
            <button wire:click="$set('isOpen', false)" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

        <form wire:submit.prevent="guardarDictamen" class="space-y-5">
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Dictamen</label>
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" wire:click="$set('dictamen', 'Aprobar')"
                        class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $dictamen === 'Aprobar' ? 'border-green-500 bg-green-50 text-green-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span class="text-xs">Aprobar</span>
                    </button>

                    <button type="button" wire:click="$set('dictamen', 'Solicitar información')"
                        class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $dictamen === 'Solicitar información' ? 'border-amber-500 bg-amber-50 text-amber-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs">Info. faltante</span>
                    </button>

                    <button type="button" wire:click="$set('dictamen', 'Rechazar')"
                        class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $dictamen === 'Rechazar' ? 'border-rose-500 bg-rose-50 text-rose-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM6 6l12 12" />
                        </svg>
                        <span class="text-xs">Rechazar</span>
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
                <button type="submit" class="text-xs font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-xl shadow-md transition">Aplicar</button>
            </div>
        </form>
    </x-modal>
</div>