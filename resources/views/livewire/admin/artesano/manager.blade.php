<?php

use App\Services\Artesanos\ArtesanosDataService;
use function Livewire\Volt\{computed};

$colaVerificacion = computed(fn() => app(ArtesanosDataService::class)->colaVerificacion());
?>

<div class="space-y-8" x-on:artesano-actualizado.window="$wire.$refresh()">

    {{-- Cola de verificación --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-cormorant text-2xl text-[#D81B60]">Cola de verificación</h3>
            <span class="bg-[#D81B60]/10 text-[#D81B60] px-3 py-1 rounded-full text-xs font-semibold">
                {{ count($this->colaVerificacion) }} solicitudes pendientes
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($this->colaVerificacion as $artesano)
            <x-admin.artisan-queue-card :artesano="$artesano" />
            @endforeach

            <div class="bg-[#D81B60]/5 border-2 border-dashed border-[#D81B60]/20 p-5 rounded-2xl flex flex-col items-center justify-center text-center">
                <div class="w-11 h-11 rounded-full bg-[#D81B60]/10 flex items-center justify-center mb-2 text-[#D81B60]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-bold text-[#D81B60]">Ver revisiones pasadas</p>
                <p class="text-xs text-neutral-500 mt-1">Consulta artesanos aprobados o rechazados recientemente</p>
            </div>
        </div>
    </section>

    {{-- Socios activos --}}
    <section>
        <h3 class="font-cormorant text-2xl text-neutral-900 mb-4">Socios artesanos activos</h3>
        <livewire:admin.artesano.table />
    </section>

    <livewire:admin.artesano.form />
</div>