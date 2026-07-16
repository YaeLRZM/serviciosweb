<?php

use App\Services\Artesanos\ArtesanosDataService;
use function Livewire\Volt\{state, computed};

state([
    'busqueda' => '',
    'page' => 1,
]);

$dataset = computed(fn() => app(ArtesanosDataService::class)->activos());

$filtered = computed(function () {
    $perPage = 10;

    $items = collect($this->dataset)
        ->when($this->busqueda !== '', fn($q) => $q->filter(
            fn($item) => str_contains(mb_strtolower($item['nombre']), mb_strtolower($this->busqueda))
                || str_contains(mb_strtolower($item['especialidad']), mb_strtolower($this->busqueda))
        ))
        ->sortByDesc('ventas_total') // TODO: hacer dinámico según el dropdown de "Sort"
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

$alternarDestacado = function ($id) {
    app(ArtesanosDataService::class)->alternarDestacado($id);
    unset($this->dataset);
    session()->flash('mensaje', 'Estado destacado actualizado.');
};
?>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-neutral-100" x-on:artesano-actualizado.window="$wire.$refresh()">

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-neutral-100">
        <div class="relative w-full max-w-xs">
            <svg class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                wire:model.live.debounce.400ms="busqueda"
                type="text"
                placeholder="Buscar por nombre o especialidad..."
                class="w-full text-sm rounded-full border-neutral-200 bg-neutral-50 pl-9 pr-3 py-2 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
        </div>

        <div class="flex gap-2">
            <button class="px-4 py-2 rounded-full bg-white border border-neutral-200 text-xs font-semibold text-neutral-600 flex items-center gap-1.5 hover:bg-neutral-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                Filtrar
            </button>
            <button class="px-4 py-2 rounded-full bg-white border border-neutral-200 text-xs font-semibold text-neutral-600 flex items-center gap-1.5 hover:bg-neutral-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                </svg>
                Orden: Mayor venta
            </button>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-neutral-50 border-b border-neutral-100">
                <tr class="text-[11px] font-bold uppercase tracking-widest text-neutral-400">
                    <th class="px-5 py-3">Perfil</th>
                    <th class="px-5 py-3">Especialidad</th>
                    <th class="px-5 py-3">Verificación</th>
                    <th class="px-5 py-3">Ventas totales</th>
                    <th class="px-5 py-3">Rating</th>
                    <th class="px-5 py-3">Destacado</th>
                    <th class="px-5 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($this->filtered['items'] as $item)
                <tr class="hover:bg-neutral-50/60 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item['foto'] }}" class="w-11 h-11 rounded-full object-cover" alt="{{ $item['nombre'] }}" />
                            <div>
                                <p class="font-bold text-neutral-800">{{ $item['nombre'] }}</p>
                                <p class="text-xs text-neutral-400">{{ $item['ubicacion'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-neutral-600">{{ $item['especialidad'] }}</td>
                    <td class="px-5 py-4">
                        @if ($item['verificado'])
                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verificado
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En revisión
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-bold text-neutral-800">${{ number_format($item['ventas_total'], 2) }}</p>
                        <p class="text-xs text-neutral-400">{{ $item['ventas_items'] }} artículos vendidos</p>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-1 text-amber-500">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.286 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.445a1 1 0 00-1.176 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.062 9.385c-.784-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z" />
                            </svg>
                            <span class="font-bold text-neutral-800">{{ $item['rating'] }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <button
                            wire:click="alternarDestacado({{ $item['id'] }})"
                            class="relative w-11 h-6 rounded-full transition-colors {{ $item['destacado'] ? 'bg-[#D81B60]' : 'bg-neutral-200' }}">
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform {{ $item['destacado'] ? 'translate-x-5' : '' }}"></span>
                        </button>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <button
                                wire:click="$dispatch('abrirRevisionArtesano', { id: {{ $item['id'] }} })"
                                class="w-8 h-8 rounded-full hover:bg-[#D81B60]/10 flex items-center justify-center text-[#D81B60] transition"
                                title="Editar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                            </button>

                            <a href="mailto:" class="w-8 h-8 rounded-full hover:bg-[#D81B60]/10 flex items-center justify-center text-[#D81B60] transition" title="Mensaje">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </a>

                            <x-dropdown align="right" width="44">
                                <x-slot name="trigger">
                                    <button class="w-8 h-8 rounded-full hover:bg-rose-50 flex items-center justify-center text-rose-500 transition" title="Más opciones">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 6a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link href="#">Ver perfil</x-dropdown-link>
                                    <button class="w-full text-start">
                                        <x-dropdown-link>Suspender</x-dropdown-link>
                                    </button>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-neutral-400 text-sm">
                        No hay artesanos que coincidan con la búsqueda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="bg-neutral-50 p-5 flex flex-wrap justify-between items-center gap-3">
        <p class="text-xs text-neutral-500">Mostrando {{ $this->filtered['from'] }}-{{ $this->filtered['to'] }} de {{ $this->filtered['total'] }} socios activos</p>
        <div class="flex gap-2">
            <button wire:click="irAPagina({{ $this->filtered['page'] - 1 }})" @disabled($this->filtered['page'] <= 1)
                    class="w-9 h-9 rounded-full border border-neutral-200 flex items-center justify-center disabled:opacity-30 hover:bg-white transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
            </button>

            @for ($p = 1; $p <= $this->filtered['totalPages']; $p++)
                <button wire:click="irAPagina({{ $p }})"
                    class="w-9 h-9 rounded-full text-sm font-medium transition {{ $p === $this->filtered['page'] ? 'bg-[#D81B60] text-white' : 'border border-neutral-200 hover:bg-white text-neutral-600' }}">
                    {{ $p }}
                </button>
                @endfor

                <button wire:click="irAPagina({{ $this->filtered['page'] + 1 }})" @disabled($this->filtered['page'] >= $this->filtered['totalPages'])
                    class="w-9 h-9 rounded-full border border-neutral-200 flex items-center justify-center disabled:opacity-30 hover:bg-white transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
        </div>
    </div>
</div>