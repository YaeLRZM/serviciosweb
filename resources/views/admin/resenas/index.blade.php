<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-cormorant text-2xl text-neutral-900">Reseñas</h2>
    </x-slot>

    <div class="-m-6 p-6 min-h-full space-y-4">
        <div class="rounded-2xl bg-white border border-neutral-100 shadow-sm px-5 py-4">
            <p class="text-sm text-neutral-600 leading-relaxed">
                Consulta todas las opiniones de la plataforma. Identifica calificaciones bajas,
                ubica la prenda y el vendedor, y cruza con la compra relacionada cuando exista.
            </p>
        </div>

        <livewire:admin.resenas.manager />
    </div>
</x-admin-layout>
