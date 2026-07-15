<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-cormorant text-2xl text-neutral-900">Vendedores</h2>
    </x-slot>

    <div class="-m-6 p-6 min-h-full space-y-4">
        @if (session()->has('mensaje'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-xl font-medium">
            {{ session('mensaje') }}
        </div>
        @endif

        <livewire:admin.vendedor.manager />
    </div>
</x-admin-layout>
