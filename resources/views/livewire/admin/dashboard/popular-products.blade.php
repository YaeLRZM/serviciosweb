<?php

use function Livewire\Volt\{with};

with(fn() => [
    'productos' => [
        [
            'inicial' => 'H',
            'nombre' => 'Huipil de Gala',
            'descripcion' => 'Bordado a mano',
            'ventas' => '142 unidades'
        ],
        [
            'inicial' => 'G',
            'nombre' => 'Guayabera Lino',
            'descripcion' => 'Talla L - Blanca',
            'ventas' => '98 unidades'
        ],
        [
            'inicial' => 'R',
            'nombre' => 'Rebozo de Seda',
            'descripcion' => 'Teñido natural',
            'ventas' => '76 unidades'
        ],
    ]
]);
?>

<!-- Ajustado a px-8 para ensanchar un poco la tarjeta en armonía con las alertas -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-6 px-8">
    <h3 class="text-xl font-bold text-[#2B2B2B] mb-6">Prendas Populares</h3>

    <div class="flex justify-between text-sm text-gray-500 mb-4 px-2">
        <span>Producto</span>
        <span>Total de ventas</span>
    </div>

    <div class="space-y-4 mb-6">
        @foreach($productos as $producto)
        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg transition">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-[#F8F5F2] rounded-md flex items-center justify-center text-[#D81B60] font-bold">
                    {{ $producto['inicial'] }}
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ $producto['nombre'] }}</p>
                    <p class="text-xs text-gray-500">{{ $producto['descripcion'] }}</p>
                </div>
            </div>
            <span class="font-bold text-gray-800 text-sm">{{ $producto['ventas'] }}</span>
        </div>
        @endforeach
    </div>

    <button class="w-full py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
        Ver catálogo completo
    </button>
</div>