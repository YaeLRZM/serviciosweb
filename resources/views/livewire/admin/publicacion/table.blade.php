<?php

use App\Services\Publicaciones\PublicacionesDataService;
use function Livewire\Volt\{state, computed};

state([
    'estadoFiltro' => '', // Cambiamos 'categoria' por 'estadoFiltro'
    'busquedaArtesano' => '',
    'page' => 1,
    'error' => null,
]);

// Nuevas opciones de filtrado por estado
$opcionesEstado = computed(fn() => [
    'PENDIENTE'  => 'Publicaciones Pendientes',
    'SUSPENDIDO' => 'Publicaciones Suspendidas',
    'REVISADO'   => 'Publicaciones Revisadas',
    'ELIMINADO'  => 'Publicaciones Eliminadas',
]);

$statusBadges = computed(fn() => [
    'PENDIENTE'  => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    'SUSPENDIDO' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
    'REVISADO'   => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    'ELIMINADO'  => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
]);

$dataset = computed(function () {
    try {
        $data = app(PublicacionesDataService::class)->reportadas();
        $this->error = null;
        return $data;
    } catch (\Throwable $e) {
        $this->error = 'No se pudieron cargar las publicaciones. Intenta de nuevo.';
        return [];
    }
});

$filtered = computed(function () {
    $perPage = 8;

    $items = collect($this->dataset)
        // Filtramos por estado si se ha seleccionado una opción
        ->when($this->estadoFiltro !== '', fn($q) => $q->where('estado', $this->estadoFiltro))
        ->when($this->busquedaArtesano !== '', fn($q) => $q->filter(
            fn($item) => str_contains(mb_strtolower($item['artesano']), mb_strtolower($this->busquedaArtesano))
        ))
        ->values();

    $total = $items->count();
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $this->page), $totalPages);

    return [
        'items'      => $items->slice(($page - 1) * $perPage, $perPage)->values(),
        'total'      => $total,
        'totalPages' => $totalPages,
        'page'       => $page,
        'from'       => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
        'to'         => min($page * $perPage, $total),
    ];
});

$irAPagina = function ($p) {
    $this->page = $p;
};
?>

<div class="bg-white rounded-3xl border border-neutral-100 shadow-sm overflow-hidden" x-on:publicacion-actualizada.window="$wire.$refresh()">

    @if ($error)
    <div class="m-5 bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-xl font-bold">
        {{ $error }}
    </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-neutral-100">
        <div class="flex flex-wrap items-center gap-3">
            <select
                wire:model.live="estadoFiltro"
                class="text-sm rounded-xl border-neutral-200 bg-neutral-50 text-neutral-700 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                <option value="">Ordenar por</option>
                @foreach ($this->opcionesEstado as $valor => $etiqueta)
                <option value="{{ $valor }}">{{ $etiqueta }}</option>
                @endforeach
            </select>

            <div class="relative">
                <input
                    wire:model.live.debounce.400ms="busquedaArtesano"
                    type="text"
                    placeholder="Filtrar por artesano..."
                    class="text-sm rounded-xl border-neutral-200 bg-neutral-50 pl-9 pr-3 py-2 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <div class="flex items-center gap-3 text-sm text-neutral-500">
            <span>Mostrando {{ $this->filtered['from'] }}-{{ $this->filtered['to'] }} de {{ $this->filtered['total'] }}</span>
            <div class="flex items-center gap-1">
                <button wire:click="irAPagina({{ $this->filtered['page'] - 1 }})" @disabled($this->filtered['page'] <= 1)
                        class="p-1.5 rounded-lg border border-neutral-200 disabled:opacity-30 hover:bg-neutral-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                </button>
                <button wire:click="irAPagina({{ $this->filtered['page'] + 1 }})" @disabled($this->filtered['page'] >= $this->filtered['totalPages'])
                    class="p-1.5 rounded-lg border border-neutral-200 disabled:opacity-30 hover:bg-neutral-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-bold text-neutral-400 uppercase tracking-wider">
                    <th class="px-5 py-3">Producto</th>
                    <th class="px-5 py-3">Categoría</th>
                    <th class="px-5 py-3">Artesano</th>
                    <th class="px-5 py-3">Enviado</th>
                    <th class="px-5 py-3">Estado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($this->filtered['items'] as $item)
                <tr class="hover:bg-neutral-50/60 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#D81B60]/10 flex items-center justify-center text-[#D81B60] font-bold text-xs shrink-0">
                                {{ mb_substr($item['producto'], 0, 1) }}
                            </div>
                            <div>
                                <div class="font-medium text-neutral-800">{{ $item['producto'] }}</div>
                                <div class="text-xs text-neutral-400">#{{ $item['codigo'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-medium bg-neutral-100 text-neutral-600 px-2.5 py-1 rounded-full">
                            {{ $item['categoria'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-neutral-600">{{ $item['artesano'] }}</td>
                    <td class="px-5 py-3 text-neutral-500">{{ $item['fecha'] }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $this->statusBadges[$item['estado']] ?? 'bg-neutral-100 text-neutral-600' }}">
                            {{ $item['estado'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <button
                            wire:click="$dispatch('abrirDetallePublicacion', { id: {{ $item['id'] }} })"
                            @disabled($item['estado']==='REVISADO' )
                            class="text-xs font-bold px-4 py-2 rounded-xl transition
                                {{ $item['estado'] === 'REVISADO'
                                    ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed'
                                    : 'bg-[#D81B60]/10 text-[#D81B60] hover:bg-[#D81B60] hover:text-white' }}"
                            title="{{ $item['estado'] === 'REVISADO' ? 'Esta publicación ya fue revisada' : 'Revisar publicación' }}">
                            Revisar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-neutral-400 text-sm">
                        No hay publicaciones que coincidan con el filtro.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-center gap-2 p-5 border-t border-neutral-100">
        @for ($p = 1; $p <= $this->filtered['totalPages']; $p++)
            <button
                wire:click="irAPagina({{ $p }})"
                class="w-8 h-8 rounded-full text-sm font-medium transition
                {{ $p === $this->filtered['page'] ? 'bg-[#D81B60] text-white' : 'text-neutral-500 hover:bg-neutral-100' }}">
                {{ $p }}
            </button>
            @endfor
    </div>
</div>