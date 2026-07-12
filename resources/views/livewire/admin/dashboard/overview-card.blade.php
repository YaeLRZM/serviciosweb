<?php

use function Livewire\Volt\{state, with};

state(['filtro_tiempo' => 'Todo el tiempo']);

with(fn() => [
    'estadisticas' => [
        'clientes_activos' => '10,243',
        'clientes_crecimiento' => '~8%',
        'ventas' => '5,800',
        'ventas_crecimiento' => '~8%',
    ],
    'artesanos' => [
        ['nombre' => 'Juana V.', 'color' => 'D81B60'],
        ['nombre' => 'Pedro L.', 'color' => '4338CA'],
        ['nombre' => 'María C.', 'color' => '0D9488'],
        ['nombre' => 'Rosa M.', 'color' => 'EA580C'],
    ]
]);

?>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6 md:p-8">
    <!-- Encabezado original con el selector -->
    <div class="flex justify-between items-center mb-8">
        <h3 class="text-xl font-bold text-[#2B2B2B]">Vista General</h3>
        <select wire:model.live="filtro_tiempo" class="bg-gray-50 border-none text-sm text-gray-600 rounded-lg focus:ring-[#D81B60] py-2 px-4 cursor-pointer">
            <option value="Todo el tiempo">Todo el tiempo</option>
            <option value="Este mes">Este mes</option>
            <option value="Esta semana">Esta semana</option>
        </select>
    </div>

    <!-- Tarjetas de Métricas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
        <div class="bg-[#F2F7F9] rounded-3xl p-6 sm:p-8">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Clientes Activos</p>
            <div class="flex items-baseline gap-3">
                <p class="text-5xl font-extrabold text-[#D81B60]">{{ $estadisticas['clientes_activos'] }}</p>
                <span class="text-emerald-500 text-sm font-bold">{{ $estadisticas['clientes_crecimiento'] }}</span>
            </div>
        </div>

        <div class="bg-[#F2F7F9] rounded-3xl p-6 sm:p-8">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ventas Realizadas</p>
            <div class="flex items-baseline gap-3">
                <p class="text-5xl font-extrabold text-[#D81B60]">{{ $estadisticas['ventas'] }}</p>
                <span class="text-emerald-500 text-sm font-bold">{{ $estadisticas['ventas_crecimiento'] }}</span>
            </div>
        </div>
    </div>

    <!-- Artesanos Destacados (Óvalos más grandes) -->
    <div>
        <h4 class="text-sm font-bold text-gray-700 mb-5">Artesanos destacados</h4>
        <div class="flex flex-wrap gap-4">
            @foreach ($artesanos as $artesano)
            <!-- Aumentamos py-1.5, pr-6, gap-3 e hicimos la imagen y el texto más grandes -->
            <div class="flex items-center gap-5 bg-white border border-gray-100 rounded-full pr-6 py-1.5 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($artesano['nombre']) }}&background={{ $artesano['color'] }}&color=fff&rounded=true" alt="Artesano" class="w-10 h-10 rounded-full">
                <span class="text-sm font-bold text-gray-700">{{ $artesano['nombre'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>