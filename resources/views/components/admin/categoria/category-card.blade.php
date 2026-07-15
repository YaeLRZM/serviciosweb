<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>
@props(['categoria'])

@php
$visible = $categoria['visible'];
@endphp

<div class="bg-white rounded-2xl border border-neutral-100 shadow-sm overflow-hidden flex flex-col">

    <div class="relative h-36 bg-neutral-100">
        @if (! empty($categoria['imagen']))
        <img
            src="{{ $categoria['imagen'] }}"
            alt="{{ $categoria['nombre'] }}"
            class="w-full h-full object-cover {{ $visible ? '' : 'grayscale opacity-60' }}" />
        @else
        <div class="w-full h-full flex items-center justify-center text-neutral-300">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 4.5h18M3.75 4.5v15a.75.75 0 00.75.75h15a.75.75 0 00.75-.75v-15" />
            </svg>
        </div>
        @endif

        <span class="absolute top-3 right-3 text-[11px] font-semibold px-2.5 py-1 rounded-full flex items-center gap-1
            {{ $visible ? 'bg-white/90 text-emerald-600' : 'bg-white/90 text-neutral-500' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $visible ? 'bg-emerald-500' : 'bg-neutral-400' }}"></span>
            {{ $visible ? 'Visible' : 'Oculta' }}
        </span>

        <div class="absolute bottom-3 left-4">
            <div class="text-white font-semibold text-lg drop-shadow-sm">{{ $categoria['nombre'] }}</div>
        </div>

        @unless ($visible)
        <button
            wire:click="alternarVisibilidad({{ $categoria['id'] }})"
            class="absolute inset-0 flex items-center justify-center bg-black/30 text-white text-sm font-semibold opacity-0 hover:opacity-100 transition">
            Restaurar al catálogo
        </button>
        @endunless
    </div>

    <div class="p-4 flex-1 flex flex-col">
        @if (! empty($categoria['descripcion']))
        <p class="text-xs text-neutral-500 line-clamp-2">{{ $categoria['descripcion'] }}</p>
        @else
        <p class="text-xs text-neutral-300 italic">Sin descripción</p>
        @endif

        <div class="flex items-center gap-2 mt-4">

            <button
                wire:click="$dispatch('abrirCategoria', { id: {{ $categoria['id'] }} })"
                class="flex-1 flex items-center justify-center gap-1.5 text-xs font-semibold text-neutral-600 bg-neutral-100 hover:bg-neutral-200 rounded-full py-2 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                </svg>
                Editar
            </button>

            <button
                wire:click="alternarVisibilidad({{ $categoria['id'] }})"
                class="p-2 rounded-full text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition"
                title="{{ $visible ? 'Ocultar' : 'Mostrar' }}">
                @if ($visible)
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                @else
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
                @endif
            </button>

            <button
                wire:click="eliminar({{ $categoria['id'] }})"
                wire:confirm="¿Seguro que quieres eliminar esta categoría?"
                class="p-2 rounded-full text-neutral-400 hover:text-rose-500 hover:bg-rose-50 transition"
                title="Eliminar">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
            </button>
        </div>
    </div>
</div>