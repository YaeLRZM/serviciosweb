<?php

use App\Services\Vendedores\VendedoresDataService;
use function Livewire\Volt\{state, on};

state([
    'isOpen' => false,
    'vendedorId' => null,
    'vendedor' => null,
    'notas' => '',
    'error' => null,
    'procesando' => false,
]);

on(['abrirVendedor' => function ($id) {
    $this->vendedorId = (int) $id;
    $this->error = null;
    $this->notas = '';
    $this->procesando = false;

    try {
        $this->vendedor = app(VendedoresDataService::class)->find($this->vendedorId);

        if (! $this->vendedor) {
            $this->error = 'No se encontró el vendedor solicitado.';
        } else {
            $this->notas = $this->vendedor['notas'] ?? '';
        }
    } catch (\Throwable $e) {
        $this->error = 'No se pudo cargar el detalle del vendedor.';
        $this->vendedor = null;
    }

    $this->isOpen = true;
}]);

$cerrar = function () {
    $this->isOpen = false;
    $this->vendedor = null;
    $this->vendedorId = null;
    $this->error = null;
    $this->notas = '';
    $this->procesando = false;
};

/**
 * Acepta o rechaza la solicitud / actualiza estatus.
 * Aceptar  → Verificado
 * Rechazar → Rechazado
 */
$aplicarDictamen = function (string $accion) {
    if (! $this->vendedorId || $this->procesando) {
        return;
    }

    $estatus = match ($accion) {
        'aceptar' => 'Verificado',
        'rechazar' => 'Rechazado',
        default => null,
    };

    if (! $estatus) {
        return;
    }

    $this->procesando = true;
    $this->error = null;

    try {
        app(VendedoresDataService::class)->actualizarEstatus($this->vendedorId, $estatus);
    } catch (\Throwable $e) {
        $this->error = 'No se pudo aplicar el dictamen. Intenta de nuevo.';
        $this->procesando = false;

        return;
    }

    $this->isOpen = false;
    $this->vendedor = null;
    $this->vendedorId = null;
    $this->procesando = false;
    $this->dispatch('vendedor-actualizado');

    $mensajes = [
        'Verificado' => 'Solicitud aceptada. El vendedor quedó verificado.',
        'Rechazado' => 'Solicitud rechazada.',
    ];

    session()->flash('mensaje', $mensajes[$estatus] ?? 'Dictamen aplicado.');
};
?>

<div>
    <x-modal
        :show="$isOpen"
        maxWidth="lg"
        :title="$vendedor['tienda'] ?? 'Revisión de vendedor'"
        :subtitle="$vendedor ? ('Propietario: ' . $vendedor['propietario']) : null">
        <x-slot name="closeButton">
            <button type="button" wire:click="cerrar" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

        @if ($error && ! $vendedor)
        <div class="space-y-4">
            <div class="bg-red-50 text-red-700 text-sm rounded-xl p-4 font-medium">{{ $error }}</div>
            <button type="button" wire:click="cerrar" class="w-full text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
                Cerrar
            </button>
        </div>
        @elseif ($vendedor)
        <div class="space-y-5">
            @if ($error)
            <div class="bg-red-50 text-red-700 text-xs rounded-xl p-3 font-medium">{{ $error }}</div>
            @endif

            <div class="flex items-start gap-4">
                <img
                    src="{{ $vendedor['imagen'] }}"
                    alt="{{ $vendedor['tienda'] }}"
                    class="w-16 h-16 rounded-2xl object-cover border border-neutral-200 shrink-0" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-neutral-900 truncate">{{ $vendedor['propietario'] }}</p>
                    <p class="text-xs text-neutral-400 mt-0.5">{{ $vendedor['email'] ?: 'Sin correo' }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="text-[11px] font-medium bg-neutral-100 text-neutral-600 px-2.5 py-1 rounded-full">
                            {{ $vendedor['categoria'] }}
                        </span>
                        <span class="text-[11px] text-neutral-400">Ingreso: {{ $vendedor['ingreso'] }}</span>
                        @if (! empty($vendedor['reportado']))
                        <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full">Reportado</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 mb-1">Estatus actual</p>
                    <p class="font-semibold text-neutral-800">{{ $vendedor['estatus'] }}</p>
                </div>
                <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 mb-1">Código INE</p>
                    <p class="font-semibold text-neutral-800 truncate" title="{{ $vendedor['codigo_ine'] ?: 'No disponible' }}">
                        {{ $vendedor['codigo_ine'] ?: 'No disponible' }}
                    </p>
                </div>
            </div>

            @if ($vendedor['foto_frontal_ine'] || $vendedor['foto_trasera_ine'])
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Documentación</p>
                <div class="grid grid-cols-2 gap-2">
                    @if ($vendedor['foto_frontal_ine'])
                    <img src="{{ $vendedor['foto_frontal_ine'] }}" alt="INE frontal" class="w-full h-24 object-cover rounded-xl border border-neutral-100" />
                    @endif
                    @if ($vendedor['foto_trasera_ine'])
                    <img src="{{ $vendedor['foto_trasera_ine'] }}" alt="INE reverso" class="w-full h-24 object-cover rounded-xl border border-neutral-100" />
                    @endif
                </div>
            </div>
            @endif

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Notas internas</label>
                <textarea
                    wire:model="notas"
                    rows="3"
                    placeholder="Escribe el motivo del dictamen..."
                    class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] placeholder-gray-400 transition-all"></textarea>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Dictamen</label>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        wire:click="aplicarDictamen('aceptar')"
                        wire:loading.attr="disabled"
                        wire:target="aplicarDictamen"
                        @disabled($procesando)
                        class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 border-emerald-200 bg-emerald-50 text-emerald-700 font-bold hover:bg-emerald-100 transition disabled:opacity-50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span class="text-xs">Aceptar</span>
                    </button>

                    <button
                        type="button"
                        wire:click="aplicarDictamen('rechazar')"
                        wire:confirm="¿Rechazar esta solicitud de vendedor?"
                        wire:loading.attr="disabled"
                        wire:target="aplicarDictamen"
                        @disabled($procesando)
                        class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 border-rose-200 bg-rose-50 text-rose-700 font-bold hover:bg-rose-100 transition disabled:opacity-50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM6 6l12 12" />
                        </svg>
                        <span class="text-xs">Rechazar</span>
                    </button>
                </div>
            </div>

            <div class="flex justify-end pt-1">
                <button type="button" wire:click="cerrar" class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
                    Cerrar
                </button>
            </div>
        </div>
        @endif
    </x-modal>
</div>
