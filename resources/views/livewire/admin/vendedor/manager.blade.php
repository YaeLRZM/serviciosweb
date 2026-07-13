<?php

use function Livewire\Volt\{computed};

$stats = computed(fn() => [
    'total' => 1284,       // TODO: Vendedor::count()
    'pendientes' => 42,     // TODO: Vendedor::where('estatus', 'En Revisión')->count()
    'marcados' => 8,        // TODO: Vendedor::where('estatus', 'Suspendido')->count()
    'activas' => 1215,      // TODO: Vendedor::where('activo', true)->count()
]);

// TODO: reemplazar por Vendedor::where('estatus', 'En Revisión')->latest()->take(3)->get()
$colaVerificacion = computed(fn() => collect([
    ['id' => 1, 'tienda' => 'Barro Rojo San Marcos', 'propietario' => 'Elena Juarez', 'imagen' => 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=200'],
    ['id' => 2, 'tienda' => 'Tejidos del Valle',      'propietario' => 'Mateo Ruiz',   'imagen' => 'https://images.unsplash.com/photo-1595408076683-577a0e414ed3?w=200'],
    ['id' => 3, 'tienda' => 'Taller de Alebrijes',    'propietario' => 'Isabela Cruz', 'imagen' => 'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a?w=200'],
]));
?>

<div class="space-y-6">

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-admin.stat-mini-card
            label="Vendedores Totales"
            :value="number_format($this->stats['total'])"
            trend="+12% este mes"
            trend-color="text-emerald-500"
            icon-bg="bg-[#D81B60]/10"
            icon-color="text-[#D81B60]"
            border-color="border-[#D81B60]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Solicitudes Pendientes"
            :value="$this->stats['pendientes']"
            trend="Requieren revisión inmediata"
            icon-bg="bg-amber-100"
            icon-color="text-amber-500"
            border-color="border-amber-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Vendedores Marcados"
            :value="$this->stats['marcados']"
            trend="Por disputas de calidad"
            trend-color="text-rose-500"
            icon-bg="bg-rose-100"
            icon-color="text-rose-500"
            border-color="border-rose-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h1.5V16.5h9l-.75-3 .75-3h-9V3H3z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Tiendas Activas"
            :value="number_format($this->stats['activas'])"
            trend="Operando actualmente"
            icon-bg="bg-emerald-100"
            icon-color="text-emerald-500"
            border-color="border-emerald-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 016.44 3h11.12a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72" />
            </svg>
        </x-admin.stat-mini-card>
    </div>

    {{-- Cola de verificación --}}
    <section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-cormorant text-2xl text-neutral-900">Cola de Verificación</h3>
            <button class="text-sm font-semibold text-[#D81B60] hover:underline">Ver todas las solicitudes</button>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-2">
            @foreach ($this->colaVerificacion as $vendedor)
            <x-admin.vendor-queue-card :vendedor="$vendedor" />
            @endforeach
        </div>
    </section>

    {{-- Tabla --}}
    <livewire:admin.vendedor.table />

    <livewire:admin.vendedor.form />
</div>