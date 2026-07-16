<x-admin-layout>
    <x-slot name="header">
        Publicaciones / Artículos
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        @if (session()->has('mensaje'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-xs px-4 py-3 rounded-xl font-bold shadow-sm">
            {{ session('mensaje') }}
        </div>
        @endif

        <livewire:admin.publicacion.manager />

    </div>
</x-admin-layout>