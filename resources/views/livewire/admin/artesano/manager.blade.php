<?php

use function Livewire\Volt\{computed};

// TODO: reemplazar por Artesano::where('estado_verificacion', 'pendiente')->get()
$colaVerificacion = computed(fn() => [
    ['id' => 1, 'nombre' => 'Mateo Ruiz', 'especialidad' => 'Alebrije Carving', 'foto' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200', 'estado' => 'revision', 'estadoLabel' => 'En revisión', 'accionLabel' => 'Ver detalle'],
    ['id' => 2, 'nombre' => 'Isabel Gomez', 'especialidad' => 'San Antonino Embroidery', 'foto' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200', 'estado' => 'nueva', 'estadoLabel' => 'Nueva solicitud', 'accionLabel' => 'Ver detalle'],
    ['id' => 3, 'nombre' => 'Pedro Sanchez', 'especialidad' => 'Barro Negro Pottery', 'foto' => 'https://images.unsplash.com/photo-1622037022824-0c71d511ad60?w=200', 'estado' => 'documentos', 'estadoLabel' => 'Documentación pendiente', 'accionLabel' => 'Contactar'],
]);
?>

<div class="space-y-8">

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