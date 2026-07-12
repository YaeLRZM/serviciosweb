<?php

use function Livewire\Volt\{with};

with(fn() => [
    'reportes_seguridad' => [
        [
            'tipo' => 'Publicación Sospechosa',
            'badge_color' => 'bg-amber-100 text-amber-800',
            'usuario' => '@mariana_oax',
            'motivo' => 'Posible revendedor industrial. Subió un lote de 50 "huipiles estilizados" idénticos que parecen de maquila y no hechos en telar.',
            'fecha' => 'Hace 10 min'
        ],
        [
            'tipo' => 'Vendedor Sospechoso',
            'badge_color' => 'bg-rose-100 text-rose-800',
            'usuario' => '@artesanias_premium_mx',
            'motivo' => 'Múltiples usuarios reportan que usa fotos robadas del colectivo de tejedoras de San Juan Cotzocón para vender imitaciones.',
            'fecha' => 'Hace 2 horas'
        ],
        [
            'tipo' => 'Publicación Sospechosa',
            'badge_color' => 'bg-amber-100 text-amber-800',
            'usuario' => '@artesano_anonimo',
            'motivo' => 'Denuncia de plagio. Diseños registrados de iconografía sagrada de la Mixteca alta siendo comercializados sin permiso comunitario.',
            'fecha' => 'Ayer'
        ]
    ]
]);
?>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-[380px]">
    <div class="flex justify-between items-center mb-4 flex-shrink-0">
        <h3 class="text-xl font-bold text-[#2B2B2B]">Alertas de Moderación</h3>
        <span class="bg-rose-500 text-white text-xs font-extrabold px-2 py-0.5 rounded-full animate-pulse">
            {{ count($reportes_seguridad) }} Activos
        </span>
    </div>

    <div class="space-y-4 overflow-y-auto pr-1 flex-1 scrollbar-thin scrollbar-thumb-gray-200">
        @foreach($reportes_seguridad as $index => $alerta)
        <div class="flex flex-col gap-2 {{ $index > 0 ? 'pt-4 border-t border-gray-100' : '' }}">

            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-md {{ $alerta['badge_color'] }}">
                        {{ $alerta['tipo'] }}
                    </span>
                    <span class="text-xs font-semibold text-[#4338CA]">{{ $alerta['usuario'] }}</span>
                </div>
                <span class="text-xs text-gray-400 whitespace-nowrap">{{ $alerta['fecha'] }}</span>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed">
                {{ $alerta['motivo'] }}
            </p>

            <div class="flex gap-4 mt-1 justify-end">
                <button class="text-xs font-semibold text-gray-500 hover:text-gray-700 transition">Descartar</button>
                <button class="text-xs font-bold text-rose-600 hover:text-rose-800 transition">Investigar Cuenta</button>
            </div>

        </div>
        @endforeach
    </div>
</div>