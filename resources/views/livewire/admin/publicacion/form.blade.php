<?php

use function Livewire\Volt\{state, on};

state([
    'isOpen' => false,
    'pubId' => null,
    'nuevo_estado' => 'Aprobado',
    'notas_moderacion' => ''
]);

on(['abrirModerador' => function ($id, $estado = 'Aprobado') {
    $this->pubId = $id;
    $this->isOpen = true;
    $this->nuevo_estado = $estado;
    $this->notas_moderacion = '';
}]);

$guardarDictamen = function () {
    // TODO: reemplazar por la actualización real, ej:
    // Publicacion::findOrFail($this->pubId)->update([
    //     'estado' => $this->nuevo_estado,
    //     'notas_moderacion' => $this->notas_moderacion,
    // ]);

    $this->isOpen = false;
    $this->dispatch('publicacion-actualizada'); // para que la tabla se refresque cuando exista backend
    session()->flash('mensaje', 'Dictamen guardado exitosamente.');
};
?>

<div>
    @if($isOpen)
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 max-w-md w-full p-6 overflow-hidden transform transition-all">
            <h3 class="text-lg font-bold text-gray-900">Dictamen de Moderación</h3>
            <p class="text-xs text-gray-400 mt-0.5 mb-6">Selecciona la acción correspondiente para el producto #{{ $pubId }}</p>

            <form wire:submit.prevent="guardarDictamen" class="space-y-5">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Acción Obligatoria</label>
                    <div class="grid grid-cols-3 gap-3">

                        <!-- Aprobar -->
                        <button type="button" wire:click="$set('nuevo_estado', 'Aprobado')"
                            class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $nuevo_estado === 'Aprobado' ? 'border-green-500 bg-green-50 text-green-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span class="text-xs">Aprobar</span>
                        </button>

                        <!-- En Revisión -->
                        <button type="button" wire:click="$set('nuevo_estado', 'Revision')"
                            class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $nuevo_estado === 'Revision' ? 'border-amber-500 bg-amber-50 text-amber-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs">Revisión</span>
                        </button>

                        <!-- Rechazar -->
                        <button type="button" wire:click="$set('nuevo_estado', 'Rechazado')"
                            class="flex flex-col items-center gap-2 p-3 rounded-2xl border-2 text-center transition-all {{ $nuevo_estado === 'Rechazado' ? 'border-rose-500 bg-rose-50 text-rose-700 font-bold' : 'border-gray-100 hover:bg-gray-50 text-gray-500' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM6 6l12 12" />
                            </svg>
                            <span class="text-xs">Rechazar</span>
                        </button>

                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Notas internas</label>
                    <textarea wire:model="notas_moderacion" rows="3" placeholder="Escribe el motivo del veredicto..."
                        class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] placeholder-gray-400 transition-all"></textarea>
                </div>

                <div class="flex justify-end gap-2.5 pt-2">
                    <button type="button" wire:click="$set('isOpen', false)" class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">Cerrar</button>
                    <button type="submit" class="text-xs font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-xl shadow-md transition">Aplicar</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>