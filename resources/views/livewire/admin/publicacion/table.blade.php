<?php

use App\Services\Api\ArticuloApiService;
use function Livewire\Volt\{state, computed};

state([
    'busqueda' => '',
    'page' => 1,
    'error' => null,
]);

// Trae los artículos desde GET /api/articulos (ruta pública)
$dataset = computed(function () {
    try {
        $respuesta = app(ArticuloApiService::class)->all();

        if (! $respuesta->successful()) {
            $this->error = 'No se pudieron cargar los artículos desde el API.';
            return [];
        }

        $this->error = null;
        return $respuesta->json('data', []);
    } catch (\Throwable $e) {
        $this->error = 'No se pudo conectar con el API de artículos.';
        return [];
    }
});

$filtered = computed(function () {
    $perPage = 8;

    $items = collect($this->dataset)
        ->when($this->busqueda !== '', fn ($q) => $q->filter(
            fn ($item) => str_contains(mb_strtolower($item['nombre'] ?? ''), mb_strtolower($this->busqueda))
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

<div class="bg-white rounded-3xl border border-neutral-100 shadow-sm overflow-hidden"
     x-on:articulo-actualizado.window="$wire.$refresh()">

    @if ($error)
    <div class="m-5 bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-xl font-bold">
        {{ $error }}
    </div>
    @endif

    @if (session('success'))
    <div class="m-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs px-4 py-3 rounded-xl font-bold">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-neutral-100">
        <div class="relative">
            <input
                wire:model.live.debounce.400ms="busqueda"
                type="text"
                placeholder="Buscar por nombre..."
                class="text-sm rounded-xl border-neutral-200 bg-neutral-50 pl-9 pr-3 py-2 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
            <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
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
                    <th class="px-5 py-3">Tienda</th>
                    <th class="px-5 py-3">Precio</th>
                    <th class="px-5 py-3">Stock</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($this->filtered['items'] as $item)
                <tr class="hover:bg-neutral-50/60 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#D81B60]/10 flex items-center justify-center text-[#D81B60] font-bold text-xs shrink-0">
                                {{ mb_substr($item['nombre'] ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <div class="font-medium text-neutral-800">{{ $item['nombre'] ?? 'Sin nombre' }}</div>
                                <div class="text-xs text-neutral-400">#{{ $item['id'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-medium bg-neutral-100 text-neutral-600 px-2.5 py-1 rounded-full">
                            {{ $item['categoria']['nombre'] ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-neutral-600">{{ $item['artesano']['nombre'] ?? '—' }}</td>
                    <td class="px-5 py-3 text-neutral-500">{{ $item['tienda']['nombre'] ?? '—' }}</td>
                    <td class="px-5 py-3 font-medium text-neutral-700">${{ number_format((float) ($item['precio'] ?? 0), 2) }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ (int) ($item['stock'] ?? 0) > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            {{ $item['stock'] ?? 0 }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Editar: abre el popup --}}
                            <button
                                wire:click="$dispatch('editarArticulo', { id: {{ $item['id'] }} })"
                                class="flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl bg-[#D81B60]/10 text-[#D81B60] hover:bg-[#D81B60] hover:text-white transition"
                                title="Editar artículo">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                                Editar
                            </button>

                            {{-- Eliminar: confirma y llama al destroy del API --}}
                            <button
                                wire:click="$dispatch('eliminarArticulo', { id: {{ $item['id'] }} })"
                                wire:confirm="¿Seguro que deseas eliminar este artículo? Esta acción no se puede deshacer."
                                class="flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition"
                                title="Eliminar artículo">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-neutral-400 text-sm">
                        No hay artículos que coincidan con la búsqueda.
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
