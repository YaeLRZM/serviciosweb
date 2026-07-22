<?php

use App\Services\Admin\AdminNotificationFeedService;
use function Livewire\Volt\{state, computed};

state([
    'abierto' => false,
]);

$feed = computed(function () {
    $user = auth()->user();
    if (! $user) {
        return ['items' => [], 'no_leidas' => 0, 'total' => 0];
    }

    try {
        return app(AdminNotificationFeedService::class)->feed($user, 20);
    } catch (\Throwable $e) {
        return ['items' => [], 'no_leidas' => 0, 'total' => 0, 'error' => true];
    }
});

$toggle = function () {
    $this->abierto = ! $this->abierto;
};

$cerrar = function () {
    $this->abierto = false;
};

$abrirItem = function (string $id) {
    $user = auth()->user();
    $item = collect($this->feed['items'] ?? [])->firstWhere('id', $id);
    $url = is_array($item) ? (string) ($item['url'] ?? '') : '';

    if ($user) {
        app(AdminNotificationFeedService::class)->marcarLeida($id, $user);
    }
    $this->abierto = false;

    if ($url === '') {
        $url = route('admin.dashboard');
    }

    return $this->redirect($url, navigate: true);
};

$marcarTodas = function () {
    $user = auth()->user();
    if ($user) {
        app(AdminNotificationFeedService::class)->marcarTodas($user);
    }
};
?>

<div class="relative" x-data @click.outside="$wire.cerrar()" @keydown.escape.window="$wire.cerrar()">
    <button
        type="button"
        wire:click="toggle"
        class="relative p-2 text-gray-600 hover:text-[#D81B60] transition rounded-full hover:bg-[#D81B60]/5"
        aria-label="Notificaciones">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if (($this->feed['no_leidas'] ?? 0) > 0)
        <span class="absolute top-1.5 right-1.5 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-[#D81B60] text-[10px] font-bold text-white border-2 border-white">
            {{ $this->feed['no_leidas'] > 9 ? '9+' : $this->feed['no_leidas'] }}
        </span>
        @endif
    </button>

    @if ($abierto)
    <div class="absolute right-0 z-50 mt-2 w-80 sm:w-96 max-h-[28rem] overflow-hidden rounded-2xl border border-neutral-100 bg-white shadow-xl flex flex-col">
        <div class="px-4 py-3 border-b border-neutral-50 flex items-center justify-between bg-white sticky top-0">
            <div>
                <h3 class="text-sm font-bold text-neutral-900">Novedades</h3>
                <p class="text-[11px] text-neutral-500">
                    @if (($this->feed['no_leidas'] ?? 0) > 0)
                        {{ $this->feed['no_leidas'] }} sin revisar
                    @else
                        Todo al día
                    @endif
                </p>
            </div>
            @if (($this->feed['total'] ?? 0) > 0)
            <button type="button" wire:click="marcarTodas" class="text-[11px] font-semibold text-[#D81B60] hover:underline">
                Marcar leídas
            </button>
            @endif
        </div>

        <div class="overflow-y-auto flex-1">
            @if (!empty($this->feed['error']))
            <p class="px-4 py-8 text-sm text-red-600 text-center">No se pudieron cargar las novedades.</p>
            @elseif (($this->feed['total'] ?? 0) === 0)
            <div class="px-4 py-10 text-center">
                <div class="w-11 h-11 mx-auto rounded-full bg-neutral-100 text-neutral-400 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-neutral-800">No hay novedades por ahora</p>
                <p class="text-xs text-neutral-500 mt-1">Cuando haya compras, reseñas o avisos importantes, aparecerán aquí.</p>
            </div>
            @else
            <ul class="divide-y divide-neutral-50">
                @foreach ($this->feed['items'] as $item)
                <li>
                    <button
                        type="button"
                        wire:click="abrirItem(@js($item['id']))"
                        class="w-full text-left px-4 py-3 hover:bg-[#F8F5F2] transition {{ empty($item['leida']) ? 'bg-rose-50/40' : '' }}">
                        <div class="flex items-start gap-2">
                            <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ empty($item['leida']) ? 'bg-[#D81B60]' : 'bg-neutral-200' }}"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold text-neutral-900 leading-snug">{{ $item['titulo'] }}</span>
                                    @if (!empty($item['urgente']))
                                    <span class="text-[10px] font-bold uppercase text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">Urgente</span>
                                    @endif
                                    @if (!empty($item['etiqueta']))
                                    <span class="text-[10px] font-semibold text-neutral-500 bg-neutral-100 px-1.5 py-0.5 rounded">{{ $item['etiqueta'] }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-neutral-600 mt-0.5 leading-relaxed line-clamp-2">{{ $item['mensaje'] }}</p>
                                <div class="text-[11px] text-neutral-400 mt-1">{{ $item['fecha_label'] ?? '' }}</div>
                            </div>
                        </div>
                    </button>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
    @endif
</div>
