<?php

use function Livewire\Volt\{state, with};

// Declaramos los estados interactivos
state(['filtro_tiempo' => 'Todo el tiempo']);

// Inyectamos las variables limpias a la vista Blade
with(fn() => [
    'estadisticas' => [
        'clientes_activos' => '10,243',
        'clientes_crecimiento' => '8% ↑',
        'ventas' => '5,800',
        'ventas_crecimiento' => '8% ↑',
    ],
    'artesanos' => [
        ['nombre' => 'Juana V.', 'color' => 'D81B60'],
        ['nombre' => 'Pedro L.', 'color' => '4338CA'],
        ['nombre' => 'María C.', 'color' => '0D9488'],
        ['nombre' => 'Rosa M.', 'color' => 'EA580C'],
    ]
]);

?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-[#2B2B2B]">Vista General</h3>
        <select wire:model.live="filtro_tiempo" class="bg-gray-50 border-none text-sm text-gray-600 rounded-lg focus:ring-[#D81B60]">
            <option value="Todo el tiempo">Todo el tiempo</option>
            <option value="Este mes">Este mes</option>
            <option value="Esta semana">Esta semana</option>
        </select>
    </div>

    <div class="bg-[#F8F5F2]/50 rounded-xl p-6 mb-8 flex flex-col sm:flex-row gap-6 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
        <div class="flex-1 flex justify-between items-center sm:pr-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Clientes Activos</p>
                <p class="text-3xl font-bold text-[#2B2B2B]">{{ $estadisticas['clientes_activos'] }}</p>
            </div>
            <span class="bg-teal-100 text-teal-700 text-xs font-bold px-3 py-1 rounded-full">
                {{ $estadisticas['clientes_crecimiento'] }}
            </span>
        </div>
        <div class="flex-1 flex justify-between items-center sm:pl-6 pt-6 sm:pt-0">
            <div>
                <p class="text-sm text-gray-500 mb-1">Ventas Realizadas</p>
                <p class="text-3xl font-bold text-[#2B2B2B]">{{ $estadisticas['ventas'] }}</p>
            </div>
            <span class="bg-teal-100 text-teal-700 text-xs font-bold px-3 py-1 rounded-full">
                {{ $estadisticas['ventas_crecimiento'] }}
            </span>
        </div>
    </div>

    <div>
        <p class="text-gray-600 mb-4 text-sm">Bienvenido a la nueva experiencia en línea. Artesanos destacados:</p>
        <div class="flex flex-wrap gap-6 items-center">
            @foreach ($artesanos as $artesano)
            <div class="flex flex-col items-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($artesano['nombre']) }}&background={{ $artesano['color'] }}&color=fff&rounded=true" alt="Artesano" class="w-12 h-12 rounded-full shadow-sm mb-2">
                <span class="text-xs font-medium text-gray-700">{{ $artesano['nombre'] }}</span>
            </div>
            @if (!$loop->last)
            <div class="h-8 w-px bg-gray-200 hidden sm:block"></div>
            @endif
            @endforeach
        </div>
    </div>
</div>