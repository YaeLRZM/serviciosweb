<?php

use App\Services\Vendedores\VendedoresDataService;
use function Livewire\Volt\{state, computed};

state([
    'busqueda' => '',
    'estatus' => 'Todos',
    'page' => 1,
    'error' => null,
]);

// Estatus reales en PostgreSQL: activo | inactivo
$estatuses = computed(fn () => ['Todos', 'activo', 'inactivo']);

$estatusBadges = computed(fn () => [
    'activo'   => ['dot' => 'bg-emerald-500', 'text' => 'bg-emerald-50 text-emerald-600'],
    'inactivo' => ['dot' => 'bg-amber-500',   'text' => 'bg-amber-50 text-amber-700'],
]);

$dataset = computed(function () {
    try {
        $data = app(VendedoresDataService::class)->all();
        $this->error = null;

        return collect($data);
    } catch (\Throwable $e) {
        $this->error = 'No se pudieron cargar los vendedores. Intenta de nuevo.';

        return collect();
    }
});

$filtered = computed(function () {
    $perPage = 10;

    $items = $this->dataset
        ->when($this->estatus !== 'Todos', fn ($q) => $q->where('estatus', $this->estatus))
        ->when($this->busqueda !== '', fn ($q) => $q->filter(
            fn ($item) => str_contains(mb_strtolower($item['tienda'] ?? ''), mb_strtolower($this->busqueda))
                || str_contains(mb_strtolower($item['propietario'] ?? ''), mb_strtolower($this->busqueda))
                || str_contains(mb_strtolower($item['email'] ?? ''), mb_strtolower($this->busqueda))
                || str_contains(mb_strtolower($item['codigo_ine'] ?? ''), mb_strtolower($this->busqueda))
        ))
        ->values();

    $total = $items->count();
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $this->page), $totalPages);
    $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
    $to = min($page * $perPage, $total);

    return [
        'items'      => $items->slice(($page - 1) * $perPage, $perPage)->values(),
        'total'      => $total,
        'totalPages' => $totalPages,
        'page'       => $page,
        'from'       => $from,
        'to'         => $to,
    ];
});

$irAPagina = function ($p) {
    $this->page = $p;
};
?>

<div class="bg-white rounded-2xl shadow-sm border border-neutral-100 overflow-hidden" x-on:vendedor-actualizado.window="$wire.$refresh()">

    @if ($error)
    <div class="m-5 bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 rounded-xl font-bold">
        {{ $error }}
    </div>
    @endif

    {{-- Filtros --}}
    <div class="flex flex-col md:flex-row gap-3 justify-between items-center p-5 border-b border-neutral-100">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative flex-1 md:w-72">
                <svg class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    wire:model.live.debounce.400ms="busqueda"
                    type="text"
                    placeholder="Filtrar por tienda, propietario, email o INE..."
                    class="w-full text-sm rounded-full border-neutral-200 bg-neutral-50 pl-9 pr-3 py-2 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
            </div>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="text-sm text-neutral-400">Mostrar:</span>
            <select wire:model.live="estatus" class="bg-neutral-50 border-neutral-200 rounded-full text-sm py-2 px-4 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                @foreach ($this->estatuses as $e)
                <option value="{{ $e }}">{{ $e === 'Todos' ? 'Todos los Estados' : ucfirst($e) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabla (solo columnas reales) --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-neutral-50 text-[11px] font-bold uppercase tracking-widest text-neutral-400">
                    <th class="px-5 py-4 border-b border-neutral-100">Tienda / Propietario</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Email</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Código INE</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Fecha de Ingreso</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Estado</th>
                    <th class="px-5 py-4 border-b border-neutral-100 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($this->filtered['items'] as $item)
                <tr class="hover:bg-neutral-50/60 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item['imagen'] }}" class="w-10 h-10 rounded-full object-cover border border-neutral-200" alt="{{ $item['tienda'] }}" />
                            <div>
                                <p class="font-semibold text-neutral-800">{{ $item['tienda'] }}</p>
                                <p class="text-xs text-neutral-400">{{ $item['propietario'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-neutral-600">{{ $item['email'] ?: '—' }}</td>
                    <td class="px-5 py-4 text-neutral-600 font-mono text-xs">{{ $item['codigo_ine'] ?: '—' }}</td>
                    <td class="px-5 py-4 text-neutral-500">{{ $item['ingreso'] }}</td>
                    <td class="px-5 py-4">
                        @php $eb = $this->estatusBadges[$item['estatus']] ?? ['dot' => 'bg-neutral-400', 'text' => 'bg-neutral-100 text-neutral-600']; @endphp
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $eb['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $eb['dot'] }}"></span>
                            {{ ucfirst($item['estatus']) }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <button
                                wire:click="$dispatch('abrirVendedor', { id: {{ $item['id'] }} })"
                                class="w-8 h-8 rounded-full hover:bg-[#D81B60]/10 flex items-center justify-center text-[#D81B60] transition"
                                title="Revisar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>

                            @if (! empty($item['email']))
                            <a href="mailto:{{ $item['email'] }}" class="w-8 h-8 rounded-full hover:bg-[#D81B60]/10 flex items-center justify-center text-[#D81B60] transition" title="Contactar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-neutral-400 text-sm">
                        No hay vendedores que coincidan con el filtro.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="bg-neutral-50 px-5 py-4 flex justify-between items-center">
        <p class="text-xs text-neutral-500">
            Mostrando {{ $this->filtered['from'] }} a {{ $this->filtered['to'] }} de {{ $this->filtered['total'] }} vendedores
        </p>
        <div class="flex items-center gap-1">
            <button wire:click="irAPagina({{ $this->filtered['page'] - 1 }})" @disabled($this->filtered['page'] <= 1)
                    class="p-2 rounded-full text-neutral-400 hover:bg-white disabled:opacity-30 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
            </button>

            @for ($p = 1; $p <= $this->filtered['totalPages']; $p++)
                <button wire:click="irAPagina({{ $p }})"
                    class="w-8 h-8 rounded-full text-sm font-bold transition {{ $p === $this->filtered['page'] ? 'bg-[#D81B60] text-white' : 'hover:bg-white text-neutral-600' }}">
                    {{ $p }}
                </button>
            @endfor

            <button wire:click="irAPagina({{ $this->filtered['page'] + 1 }})" @disabled($this->filtered['page'] >= $this->filtered['totalPages'])
                class="p-2 rounded-full text-neutral-400 hover:bg-white disabled:opacity-30 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</div>
