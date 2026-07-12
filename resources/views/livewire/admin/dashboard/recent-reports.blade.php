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

<!-- Cambiamos h-[340px] por h-[380px] para darle un poco más de altura -->
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6 flex flex-col h-[410px]">
    <div class="flex justify-between items-center mb-4 flex-shrink-0">
        <h3 class="text-lg font-bold text-[#D81B60] flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Alertas de Moderación
        </h3>
        <span class="bg-red-50 text-red-500 text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider">
            Urgente
        </span>
    </div>

    <!-- Contenedor con scroll (overflow-y-auto) -->
    <div class="space-y-4 flex-1 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200">
        @foreach($reportes_seguridad as $index => $alerta)
        <div class="border border-gray-100 rounded-2xl p-4 flex gap-3 relative overflow-hidden bg-[#FAFAFA]/50">
            <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $index === 0 ? 'bg-red-500' : 'bg-yellow-400' }}"></div>

            <div class="flex-1 pl-2">
                <h4 class="text-sm font-bold text-gray-800 mb-1">{{ $alerta['tipo'] }}</h4>
                <p class="text-xs text-gray-500 leading-relaxed mb-3">
                    {{ Str::limit($alerta['motivo'], 65) }}
                </p>
                <div class="flex gap-3">
                    <button class="bg-[#D81B60] text-white text-[10px] font-bold px-4 py-1.5 rounded-full hover:bg-[#ad144b] transition">
                        {{ $index === 0 ? 'Revisar' : 'Investigar' }}
                    </button>
                    @if($index === 0)
                    <button class="text-gray-400 text-[10px] font-bold px-2 py-1.5 hover:text-gray-600 transition">
                        Ignorar
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Botón moderno que agregamos en el paso anterior -->
    <div class="mt-4 text-center flex-shrink-0">
        <button class="group inline-flex items-center justify-center gap-2 w-full py-2.5 text-sm font-bold text-[#D81B60] bg-[#D81B60]/10 rounded-full hover:bg-[#D81B60] hover:text-white transition-all duration-300">
            Ver todas las alertas
            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </button>
    </div>
</div>