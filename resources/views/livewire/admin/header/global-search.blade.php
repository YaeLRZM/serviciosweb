<?php

use App\Services\Admin\AdminGlobalSearchService;
use function Livewire\Volt\{state, computed};

state([
    'q' => '',
    'abierto' => false,
]);

$resultados = computed(function () {
    $q = trim($this->q);
    if (mb_strlen($q) < 2) {
        return ['query' => $q, 'total' => 0, 'groups' => [], 'corto' => true];
    }

    try {
        $ruta = request()->route()?->getName();
        $data = app(AdminGlobalSearchService::class)->buscar($q, $ruta, 5);
        $data['corto'] = false;

        return $data;
    } catch (\Throwable $e) {
        return ['query' => $q, 'total' => 0, 'groups' => [], 'corto' => false, 'error' => true];
    }
});

$abrir = function () {
    $this->abierto = true;
};

$cerrar = function () {
    $this->abierto = false;
};

$limpiar = function () {
    $this->q = '';
    $this->abierto = false;
};

$updatedQ = function () {
    $this->abierto = true;
};
?>

<div
    class="relative hidden sm:block w-72 md:w-96"
    x-data
    @click.outside="$wire.cerrar()"
    @keydown.escape.window="$wire.cerrar()">

    <div class="relative">
        <svg class="w-4 h-4 text-neutral-400 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
            type="search"
            wire:model.live.debounce.300ms="q"
            wire:focus="abrir"
            placeholder="Buscar prendas, compras, tiendas..."
            autocomplete="off"
            class="w-full pl-11 pr-10 py-2.5 bg-white border border-pink-100/80 rounded-full text-sm text-neutral-700 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] transition-colors shadow-sm" />
        @if (trim($q) !== '')
        <button type="button" wire:click="limpiar" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-neutral-400 hover:text-neutral-700" title="Limpiar">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        @endif
    </div>

    @if ($abierto && mb_strlen(trim($q)) >= 1)
    <div class="absolute z-50 mt-2 w-full md:w-[28rem] max-h-[28rem] overflow-y-auto rounded-2xl border border-neutral-100 bg-white shadow-xl right-0">
        <div class="px-4 py-2.5 border-b border-neutral-50 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur">
            <span class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Resultados</span>
            <span wire:loading wire:target="q" class="text-[11px] text-[#D81B60] font-semibold">Buscando…</span>
        </div>

        @if (mb_strlen(trim($q)) < 2)
        <p class="px-4 py-6 text-sm text-neutral-500 text-center">Escribe al menos 2 letras para buscar.</p>
        @elseif (!empty($this->resultados['error']))
        <p class="px-4 py-6 text-sm text-red-600 text-center">No se pudo completar la búsqueda. Intenta de nuevo.</p>
        @elseif (($this->resultados['total'] ?? 0) === 0)
        <div class="px-4 py-8 text-center">
            <p class="text-sm font-semibold text-neutral-800">No se encontraron coincidencias</p>
            <p class="text-xs text-neutral-500 mt-1">Prueba con otro nombre, correo o número de compra.</p>
        </div>
        @else
        <div class="py-2">
            @foreach ($this->resultados['groups'] as $grupo)
            <div class="px-3 pt-2 pb-1">
                <div class="text-[10px] font-bold uppercase tracking-wider text-[#D81B60] px-2 mb-1">{{ $grupo['etiqueta'] }}</div>
                <ul class="space-y-0.5">
                    @foreach ($grupo['items'] as $item)
                    <li>
                        <a
                            href="{{ $item['url'] }}"
                            wire:navigate
                            wire:click="cerrar"
                            class="block rounded-xl px-3 py-2.5 hover:bg-[#F8F5F2] transition">
                            <div class="text-sm font-semibold text-neutral-900 leading-snug">{{ $item['titulo'] }}</div>
                            <div class="text-xs text-neutral-500 mt-0.5 line-clamp-1">{{ $item['subtitulo'] }}</div>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
        <div class="px-4 py-2 border-t border-neutral-50 text-[11px] text-neutral-400">
            {{ $this->resultados['total'] }} resultado(s)
        </div>
        @endif
    </div>
    @endif
</div>
