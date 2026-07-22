<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-cormorant text-2xl text-neutral-900">Ventas generales</h2>
    </x-slot>

    <div class="-m-6 p-6 min-h-full space-y-4">
        <div class="rounded-2xl bg-white border border-neutral-100 shadow-sm px-5 py-4">
            <p class="text-sm text-neutral-600 leading-relaxed">
                Supervisa las compras de <span class="font-semibold text-neutral-800">todos los vendedores</span>.
                Filtra por fechas, tienda, cliente o estado para aclarar un caso y ver el detalle de cada compra.
            </p>
        </div>

        <livewire:admin.ventas.manager />
    </div>
</x-admin-layout>
