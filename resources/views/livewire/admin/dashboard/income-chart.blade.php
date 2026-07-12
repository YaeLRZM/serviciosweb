<?php

use function Livewire\Volt\{state, with};

state(['categoria_filtro' => 'Textiles']);

with(fn() => [
    'regiones' => ['Valles', 'Istmo', 'Costa', 'Sierra Sur', 'Sierra Norte', 'Papaloapan', 'Cañada', 'Mixteca'],
    'ventas_por_region' => [1250, 980, 740, 420, 310, 680, 210, 550],
    // Colores artesanales asignados a cada barra individualmente
    'colores_regiones' => [
        '#E65C00', // Cempasúchil / Naranja textil
        '#D81B60', // Rosa Bugambilia intenso
        '#2E8B57', // Verde Jade / Orgánico
        '#EAB308', // Amarillo Girasol / Ocre
        '#990000', // Rojo Carmín / Grana Cochinilla
        '#008080', // Azul Añil / Turquesa obscuro
        '#E65C00', // Cempasúchil alternado
        '#D81B60'  // Bugambilia alternado
    ],
    'prendas_top' => [
        'Valles' => 'Tapetes de Teotitlán / Blusas de San Antonino',
        'Istmo' => 'Huipiles de Gala Bordados',
        'Costa' => 'Pozahuancos / Huipiles de Cortijo',
        'Sierra Sur' => 'Bordados de San Vicente Coatlán',
        'Sierra Norte' => 'Huipiles de Yalálag',
        'Papaloapan' => 'Huipiles de San Felipe Jalapa de Díaz',
        'Cañada' => 'Huipiles de Huautla de Jiménez',
        'Mixteca' => 'Camisas de Cotón / Textiles de Telar de Cintura',
    ]
]);

?>

<!-- Cambiamos rounded-2xl a rounded-[2rem] y unificamos el color de borde -->
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <!-- Cambiamos el color del encabezado a Rosa Bugambilia (#D81B60) -->
            <h3 class="text-xl font-bold text-[#D81B60]">Ventas por Región (Oaxaca)</h3>
            <p class="text-sm text-gray-500 mt-1">Regiones con mayor impacto cultural y comercial</p>
        </div>
        <select wire:model.live="categoria_filtro" class="bg-gray-50 border-none text-sm font-medium text-gray-600 rounded-lg focus:ring-[#D81B60] py-2 pl-4 pr-8 cursor-pointer">
            <option value="Textiles">Solo Textiles</option>
            <option value="Todos">Todo el Catálogo</option>
        </select>
    </div>

    <!-- Contenedor de la Gráfica - Incrementado a h-72 para mayor longitud -->
    <div class="h-72 flex items-end gap-3 pt-4 border-b border-l border-gray-100 px-4">
        @foreach($ventas_por_region as $index => $ventas)
        @php
        $region_nombre = $regiones[$index];
        $color_barra = $colores_regiones[$index];
        $max_ventas = max($ventas_por_region) ?: 1;
        $altura_porcentaje = ($ventas / $max_ventas) * 100;
        @endphp
        <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
            <!-- Tooltip -->
            <div class="absolute bottom-full mb-2 bg-[#2B2B2B] text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none text-center shadow-lg z-10 w-48">
                <p class="font-bold border-b border-gray-700 pb-1 mb-1">{{ $region_nombre }}</p>
                <p class="text-gray-300 mb-1">{{ $ventas }} prendas vendidas</p>
                <p class="text-[10px] text-[#F8F5F2] italic">Top: {{ $prendas_top[$region_nombre] }}</p>
            </div>

            <!-- Barra de Color Dinámico e Interacción -->
            <!-- Se hizo ligeramente más redondeada arriba con rounded-t-lg -->
            <div style="height: {{ $altura_porcentaje }}%; background-color: {{ $color_barra }};"
                class="w-full opacity-90 group-hover:opacity-100 group-hover:scale-x-105 rounded-t-lg transition-all duration-300 cursor-pointer">
            </div>
        </div>
        @endforeach
    </div>

    <!-- Eje X -->
    <div class="flex gap-3 mt-3 px-4">
        @foreach($regiones as $reg)
        <div class="flex-1 text-center text-[11px] font-bold text-gray-400 truncate" title="{{ $reg }}">
            {{ $reg }}
        </div>
        @endforeach
    </div>
</div>