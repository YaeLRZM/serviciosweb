<?php

use function Livewire\Volt\{state, computed};

state([
    'categoria' => 'Todas',
    'busquedaArtesano' => '',
    'page' => 1,
]);

$categorias = computed(fn() => ['Todas', 'Textiles', 'Cerámica', 'Talla en madera', 'Joyería']);

$statusBadges = computed(fn() => [
    'Pendiente' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    'Revisión'  => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
    'Aprobado'  => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    'Rechazado' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
]);

// TODO: reemplazar por Publicacion::query()->with('artesano','categoria')->...
$dataset = computed(fn() => [
    ['id' => 1,  'producto' => 'Huipil de Gala',        'codigo' => 'ART-2930', 'categoria' => 'Textiles',        'artesano' => 'María Sánchez', 'fecha' => '24 Oct 2023', 'estado' => 'Pendiente'],
    ['id' => 2,  'producto' => 'Jarrón de Barro Negro',  'codigo' => 'ART-2931', 'categoria' => 'Cerámica',        'artesano' => 'Pedro López',   'fecha' => '23 Oct 2023', 'estado' => 'Revisión'],
    ['id' => 3,  'producto' => 'Alebrije Jaguar',        'codigo' => 'ART-2935', 'categoria' => 'Talla en madera', 'artesano' => 'Juana Ruiz',    'fecha' => '23 Oct 2023', 'estado' => 'Pendiente'],
    ['id' => 4,  'producto' => 'Rebozo de Seda',         'codigo' => 'ART-2936', 'categoria' => 'Textiles',        'artesano' => 'Elena Cruz',    'fecha' => '22 Oct 2023', 'estado' => 'Aprobado'],
    ['id' => 5,  'producto' => 'Collar de Plata',        'codigo' => 'ART-2937', 'categoria' => 'Joyería',         'artesano' => 'Marcos Díaz',   'fecha' => '22 Oct 2023', 'estado' => 'Pendiente'],
    ['id' => 6,  'producto' => 'Vasija Trenzada',        'codigo' => 'ART-2938', 'categoria' => 'Cerámica',        'artesano' => 'Rosa Jiménez',  'fecha' => '21 Oct 2023', 'estado' => 'Rechazado'],
    ['id' => 7,  'producto' => 'Máscara Ceremonial',     'codigo' => 'ART-2939', 'categoria' => 'Talla en madera', 'artesano' => 'Luis Torres',   'fecha' => '21 Oct 2023', 'estado' => 'Revisión'],
    ['id' => 8,  'producto' => 'Blusa Bordada',          'codigo' => 'ART-2940', 'categoria' => 'Textiles',        'artesano' => 'Carmen Vidal',  'fecha' => '20 Oct 2023', 'estado' => 'Aprobado'],
    ['id' => 9,  'producto' => 'Arete Filigrana',        'codigo' => 'ART-2941', 'categoria' => 'Joyería',         'artesano' => 'Ana Morales',   'fecha' => '20 Oct 2023', 'estado' => 'Pendiente'],
    ['id' => 10, 'producto' => 'Plato Decorativo',       'codigo' => 'ART-2942', 'categoria' => 'Cerámica',        'artesano' => 'Jorge Ramos',   'fecha' => '19 Oct 2023', 'estado' => 'Aprobado'],
    ['id' => 11, 'producto' => 'Sombrero de Palma',      'codigo' => 'ART-2943', 'categoria' => 'Textiles',        'artesano' => 'Sofía Herrera', 'fecha' => '19 Oct 2023', 'estado' => 'Pendiente'],
    ['id' => 12, 'producto' => 'Anillo Grabado',         'codigo' => 'ART-2944', 'categoria' => 'Joyería',         'artesano' => 'Diego Salas',   'fecha' => '18 Oct 2023', 'estado' => 'Revisión'],
]);

$filtered = computed(function () {
    $perPage = 8;

    $items = collect($this->dataset)
        ->when($this->categoria !== 'Todas', fn($q) => $q->where('categoria', $this->categoria))
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

<div class="bg-white rounded-3xl border border-neutral-100 shadow-sm overflow-hidden">

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-neutral-100">
        <div class="flex flex-wrap items-center gap-3">
            <select
                wire:change="$set('categoria', $event.target.value)"
                class="text-sm rounded-xl border-neutral-200 bg-neutral-50 text-neutral-700 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                @foreach ($this->categorias as $cat)
                <option value="{{ $cat }}" @selected($categoria===$cat)>{{ $cat === 'Todas' ? 'Todas las categorías' : $cat }}</option>
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

    {{-- Tabla --}}
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
                            {{ strtoupper($item['estado']) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            <button
                                wire:click="$dispatch('abrirModerador', { id: {{ $item['id'] }} })"
                                class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition"
                                title="Ver detalle">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>

                            <button
                                wire:click="$dispatch('abrirModerador', { id: {{ $item['id'] }}, estado: 'Aprobado' })"
                                class="p-1.5 rounded-lg text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition"
                                title="Aprobar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75l6-6M9 12.75l-2.25-2.25M9 12.75L11.25 15M20.25 12a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0z" />
                                </svg>
                            </button>

                            <button
                                wire:click="$dispatch('abrirModerador', { id: {{ $item['id'] }}, estado: 'Rechazado' })"
                                class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                title="Rechazar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M20.25 12a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0z" />
                                </svg>
                            </button>
                        </div>
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

    {{-- Paginación numérica --}}
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