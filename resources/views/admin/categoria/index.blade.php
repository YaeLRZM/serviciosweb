<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-cormorant text-2xl text-neutral-900">Categorías</h2>
    </x-slot>

    <div class="-m-6 p-6 min-h-full space-y-6">
        @if (session()->has('mensaje'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-xs px-4 py-3 rounded-xl font-bold shadow-sm">
            {{ session('mensaje') }}
        </div>
        @endif

        @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-xl font-bold shadow-sm">
            {{ session('error') }}
        </div>
        @endif

        <livewire:admin.categoria.manager />
    </div>
</x-admin-layout>