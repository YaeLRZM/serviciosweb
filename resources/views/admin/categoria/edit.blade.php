<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-cormorant text-2xl text-neutral-900">Editar categoría</h2>
    </x-slot>

    <div class="bg-[#F0F4F8] -m-6 p-6 min-h-full">
        <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-neutral-100 shadow-sm p-8">
            <livewire:admin.categoria.form :categoria-id="$categoriaId" :key="'editar-'.$categoriaId" />
        </div>
    </div>
</x-admin-layout>