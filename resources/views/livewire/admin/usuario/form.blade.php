<?php

use function Livewire\Volt\{state, on};

state([
    'isOpen' => false,
    'soloLectura' => true,
    'usuarioId' => null,
    'nombre' => '',
    'email' => '',
    'rol' => 'Customer',
    'estatus' => 'Active',
]);

on(['abrirUsuario' => function ($id, $editar = false) {
    // TODO: reemplazar por User::findOrFail($id)
    $mock = [
        1 => ['nombre' => 'Alejandro Ruiz', 'email' => 'a.ruiz@example.mx', 'rol' => 'Buyer', 'estatus' => 'Flagged'],
        2 => ['nombre' => 'Elena Montes', 'email' => 'elena.m@textiles.com', 'rol' => 'Artisan', 'estatus' => 'Active'],
        3 => ['nombre' => 'Roberto Sanchez', 'email' => 'rs.92@webmail.mx', 'rol' => 'Customer', 'estatus' => 'Suspended'],
        4 => ['nombre' => 'Julian Cordoba', 'email' => 'j.cordoba@design.com', 'rol' => 'Customer', 'estatus' => 'Active'],
    ][$id] ?? null;

    if (! $mock) return;

    $this->usuarioId = $id;
    $this->nombre = $mock['nombre'];
    $this->email = $mock['email'];
    $this->rol = $mock['rol'];
    $this->estatus = $mock['estatus'];
    $this->soloLectura = ! $editar;
    $this->isOpen = true;
}]);

$guardar = function () {
    // TODO: User::findOrFail($this->usuarioId)->update([
    //     'rol' => $this->rol,
    //     'estatus' => $this->estatus,
    // ]);

    $this->isOpen = false;
    $this->dispatch('usuario-actualizado');
    session()->flash('mensaje', 'Usuario actualizado.');
};
?>

<div>
    <x-modal :show="$isOpen" :title="$nombre" :subtitle="$email">
        <x-slot name="closeButton">
            <button wire:click="$set('isOpen', false)" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

        <div class="space-y-5">
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Rol</label>
                @if ($soloLectura)
                <p class="text-sm text-neutral-700">{{ $rol }}</p>
                @else
                <select wire:model="rol" class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                    <option value="Customer">Customer</option>
                    <option value="Buyer">Buyer</option>
                    <option value="Artisan">Artisan</option>
                </select>
                @endif
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Estatus</label>
                @if ($soloLectura)
                <p class="text-sm text-neutral-700">{{ $estatus }}</p>
                @else
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" wire:click="$set('estatus', 'Active')"
                        class="p-2.5 rounded-xl border-2 text-xs font-semibold transition-all {{ $estatus === 'Active' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-100 text-gray-500 hover:bg-gray-50' }}">
                        Activo
                    </button>
                    <button type="button" wire:click="$set('estatus', 'Suspended')"
                        class="p-2.5 rounded-xl border-2 text-xs font-semibold transition-all {{ $estatus === 'Suspended' ? 'border-neutral-400 bg-neutral-100 text-neutral-700' : 'border-gray-100 text-gray-500 hover:bg-gray-50' }}">
                        Suspendido
                    </button>
                    <button type="button" wire:click="$set('estatus', 'Flagged')"
                        class="p-2.5 rounded-xl border-2 text-xs font-semibold transition-all {{ $estatus === 'Flagged' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-gray-100 text-gray-500 hover:bg-gray-50' }}">
                        Marcado
                    </button>
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" wire:click="$set('isOpen', false)" class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
                    Cerrar
                </button>

                @if (! $soloLectura)
                <button wire:click="guardar" class="text-xs font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-xl shadow-md transition">
                    Guardar
                </button>
                @endif
            </div>
        </div>
    </x-modal>
</div>