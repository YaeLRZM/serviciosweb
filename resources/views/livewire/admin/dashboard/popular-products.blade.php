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

<!-- Altura fija para alinearse con la tarjeta de arriba -->
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6 flex flex-col h-[430px]">
    <h3 class="text-lg font-bold text-[#D81B60] mb-4 flex-shrink-0">Prendas Populares</h3>

    <!-- Contenedor con scroll (overflow-y-auto) -->
    <div class="space-y-2 flex-1 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200">
        @foreach($productos as $producto)
        <div class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#F2F7F9] rounded-xl overflow-hidden flex items-center justify-center text-[#D81B60] font-bold">
                    {{ $producto['inicial'] }}
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ $producto['nombre'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Región: {{ explode(' ', $producto['descripcion'])[0] ?? 'Oaxaca' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-bold text-gray-800 text-sm">{{ (int) filter_var($producto['ventas'], FILTER_SANITIZE_NUMBER_INT) }}</p>
                <p class="text-[10px] font-bold text-emerald-500">+12%</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Botón fijado al fondo -->
    <button class="w-full py-2.5 text-sm font-bold text-[#D81B60] bg-white border border-[#D81B60]/30 rounded-full hover:bg-pink-50 transition mt-4 flex-shrink-0">
        Exportar Reporte
    </button>
</div>