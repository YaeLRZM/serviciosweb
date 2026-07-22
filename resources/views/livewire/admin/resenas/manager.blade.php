<?php

use App\Services\Admin\ResenasDataService;
use function Livewire\Volt\{state, computed, mount};

state([
    'busqueda' => '',
    'fecha_desde' => '',
    'fecha_hasta' => '',
    'calificacion' => '',
    'tienda_id' => '',
    'vendedor_id' => '',
    'artesano_id' => '',
    'cliente_id' => '',
    'producto' => '',
    'compra_id' => '',
    'recientes' => '',
    'orden' => 'fecha_desc',
    'page' => 1,
    'detalleId' => null,
    'error' => null,
    'mostrarFiltros' => true,
]);

// Filtros desde la URL (p. ej. desde alertas del dashboard).
mount(function () {
    $this->busqueda = (string) request('busqueda', '');
    $this->fecha_desde = (string) request('fecha_desde', '');
    $this->fecha_hasta = (string) request('fecha_hasta', '');
    $this->calificacion = (string) request('calificacion', '');
    $this->tienda_id = (string) request('tienda_id', '');
    $this->vendedor_id = (string) request('vendedor_id', '');
    $this->artesano_id = (string) request('artesano_id', '');
    $this->cliente_id = (string) request('cliente_id', '');
    $this->producto = (string) request('producto', '');
    $this->compra_id = (string) request('compra_id', '');
    $this->recientes = (string) request('recientes', '');
    $orden = (string) request('orden', 'fecha_desc');
    $this->orden = in_array($orden, ['fecha_desc', 'fecha_asc', 'calificacion_desc', 'calificacion_asc'], true)
        ? $orden
        : 'fecha_desc';
    if ($this->calificacion !== '' || $this->fecha_desde !== '') {
        $this->mostrarFiltros = true;
    }
});

$filtrosActivos = computed(function () {
    return array_filter([
        'busqueda' => $this->busqueda !== '' ? $this->busqueda : null,
        'fecha_desde' => $this->fecha_desde !== '' ? $this->fecha_desde : null,
        'fecha_hasta' => $this->fecha_hasta !== '' ? $this->fecha_hasta : null,
        'calificacion' => $this->calificacion !== '' ? $this->calificacion : null,
        'tienda_id' => $this->tienda_id !== '' ? $this->tienda_id : null,
        'vendedor_id' => $this->vendedor_id !== '' ? $this->vendedor_id : null,
        'artesano_id' => $this->artesano_id !== '' ? $this->artesano_id : null,
        'cliente_id' => $this->cliente_id !== '' ? $this->cliente_id : null,
        'producto' => $this->producto !== '' ? $this->producto : null,
        'compra_id' => $this->compra_id !== '' ? $this->compra_id : null,
        'recientes' => $this->recientes !== '' ? $this->recientes : null,
        'orden' => $this->orden ?: 'fecha_desc',
    ], fn ($v) => $v !== null && $v !== '');
});

$opciones = computed(function () {
    try {
        return app(ResenasDataService::class)->opcionesFiltro();
    } catch (\Throwable $e) {
        return [
            'tiendas' => [],
            'vendedores' => [],
            'artesanos' => [],
            'clientes' => [],
            'calificaciones' => [],
        ];
    }
});

$resumen = computed(function () {
    try {
        $this->error = null;

        return app(ResenasDataService::class)->resumen($this->filtrosActivos);
    } catch (\Throwable $e) {
        $this->error = 'No se pudo calcular el resumen de reseñas.';

        return [
            'total' => 0,
            'promedio' => null,
            'bajas' => 0,
            'altas' => 0,
            'recientes' => 0,
        ];
    }
});

$listado = computed(function () {
    try {
        $this->error = null;
        $svc = app(ResenasDataService::class);
        $page = max(1, (int) $this->page);
        $query = $svc->aplicarOrden(
            $svc->baseQuery($this->filtrosActivos),
            (string) ($this->filtrosActivos['orden'] ?? 'fecha_desc')
        );
        $total = (clone $query)->count();
        $perPage = 10;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $rows = $query->forPage($page, $perPage)->get();

        return [
            'items' => $rows->map(fn ($r) => $svc->mapearFila($r))->values(),
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ];
    } catch (\Throwable $e) {
        $this->error = 'No se pudieron cargar las reseñas. Intenta de nuevo.';

        return [
            'items' => collect(),
            'total' => 0,
            'totalPages' => 1,
            'page' => 1,
            'from' => 0,
            'to' => 0,
        ];
    }
});

$detalle = computed(function () {
    if (! $this->detalleId) {
        return null;
    }
    try {
        return app(ResenasDataService::class)->detalle((int) $this->detalleId);
    } catch (\Throwable $e) {
        return null;
    }
});

$irAPagina = function ($p) {
    $this->page = max(1, (int) $p);
};

$abrirDetalle = function ($id) {
    $this->detalleId = (int) $id;
};

$cerrarDetalle = function () {
    $this->detalleId = null;
};

$limpiarFiltros = function () {
    $this->busqueda = '';
    $this->fecha_desde = '';
    $this->fecha_hasta = '';
    $this->calificacion = '';
    $this->tienda_id = '';
    $this->vendedor_id = '';
    $this->artesano_id = '';
    $this->cliente_id = '';
    $this->producto = '';
    $this->compra_id = '';
    $this->recientes = '';
    $this->orden = 'fecha_desc';
    $this->page = 1;
};

$updatedBusqueda = fn () => $this->page = 1;
$updatedFecha_desde = fn () => $this->page = 1;
$updatedFecha_hasta = fn () => $this->page = 1;
$updatedCalificacion = fn () => $this->page = 1;
$updatedTienda_id = fn () => $this->page = 1;
$updatedVendedor_id = fn () => $this->page = 1;
$updatedArtesano_id = fn () => $this->page = 1;
$updatedCliente_id = fn () => $this->page = 1;
$updatedProducto = fn () => $this->page = 1;
$updatedCompra_id = fn () => $this->page = 1;
$updatedRecientes = fn () => $this->page = 1;
$updatedOrden = fn () => $this->page = 1;

?>
@php
    $estrellasTexto = function (int $n): string {
        $n = max(0, min(5, $n));

        return str_repeat('★', $n).str_repeat('☆', 5 - $n);
    };
@endphp

<div class="space-y-6">
    @if ($error)
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl font-medium">
        {{ $error }}
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <x-admin.stat-mini-card
            label="Reseñas"
            :value="number_format($this->resumen['total'])"
            trend="En el filtro actual"
            icon-bg="bg-[#D81B60]/10"
            icon-color="text-[#D81B60]"
            border-color="border-[#D81B60]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Promedio"
            :value="$this->resumen['promedio'] !== null ? number_format($this->resumen['promedio'], 1).' / 5' : '—'"
            trend="Calificación media"
            trend-color="text-amber-600"
            icon-bg="bg-amber-50"
            icon-color="text-amber-600"
            border-color="border-amber-300">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Bajas"
            :value="number_format($this->resumen['bajas'])"
            trend="1 o 2 estrellas"
            trend-color="text-rose-600"
            icon-bg="bg-rose-50"
            icon-color="text-rose-600"
            border-color="border-rose-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Altas"
            :value="number_format($this->resumen['altas'])"
            trend="4 o 5 estrellas"
            trend-color="text-emerald-600"
            icon-bg="bg-emerald-50"
            icon-color="text-emerald-600"
            border-color="border-emerald-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Recientes"
            :value="number_format($this->resumen['recientes'])"
            trend="Últimos 14 días"
            trend-color="text-sky-600"
            icon-bg="bg-sky-50"
            icon-color="text-sky-600"
            border-color="border-sky-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </x-admin.stat-mini-card>
    </div>

    <div class="bg-white rounded-2xl border border-neutral-100 shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-5 py-4 border-b border-neutral-100">
            <div>
                <h3 class="text-sm font-bold text-neutral-800">Buscar y filtrar reseñas</h3>
                <p class="text-xs text-neutral-500 mt-0.5">Encuentra opiniones por cliente, prenda, tienda o calificación.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="$toggle('mostrarFiltros')"
                    class="text-xs font-semibold px-3 py-2 rounded-full border border-neutral-200 text-neutral-600 hover:bg-neutral-50">
                    {{ $mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros' }}
                </button>
                <button type="button" wire:click="limpiarFiltros"
                    class="text-xs font-semibold px-3 py-2 rounded-full bg-[#D81B60]/10 text-[#D81B60] hover:bg-[#D81B60]/15">
                    Limpiar filtros
                </button>
            </div>
        </div>

        <div class="p-5 space-y-4">
            <div class="relative">
                <svg class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    wire:model.live.debounce.400ms="busqueda"
                    type="text"
                    placeholder="Buscar en comentarios, clientes, prendas o tiendas..."
                    class="w-full text-sm rounded-full border-neutral-200 bg-neutral-50 pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
            </div>

            @if ($mostrarFiltros)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Desde</label>
                    <input type="date" wire:model.live="fecha_desde" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Hasta</label>
                    <input type="date" wire:model.live="fecha_hasta" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Calificación</label>
                    <select wire:model.live="calificacion" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todas</option>
                        @foreach ($this->opciones['calificaciones'] as $c)
                        <option value="{{ $c['id'] }}">{{ $c['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Solo recientes</label>
                    <select wire:model.live="recientes" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">No</option>
                        <option value="si">Últimos 14 días</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Tienda</label>
                    <select wire:model.live="tienda_id" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todas</option>
                        @foreach ($this->opciones['tiendas'] as $t)
                        <option value="{{ $t['id'] }}">{{ $t['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Vendedor</label>
                    <select wire:model.live="vendedor_id" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todos</option>
                        @foreach ($this->opciones['vendedores'] as $v)
                        <option value="{{ $v['id'] }}">{{ $v['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Artesano</label>
                    <select wire:model.live="artesano_id" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todos</option>
                        @foreach ($this->opciones['artesanos'] as $a)
                        <option value="{{ $a['id'] }}">{{ $a['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Cliente</label>
                    <select wire:model.live="cliente_id" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todos</option>
                        @foreach ($this->opciones['clientes'] as $c)
                        <option value="{{ $c['id'] }}">{{ $c['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Prenda</label>
                    <input type="text" wire:model.live.debounce.400ms="producto" placeholder="Nombre de la prenda"
                        class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Nº de compra</label>
                    <input type="number" min="1" wire:model.live.debounce.400ms="compra_id" placeholder="Ej. 12"
                        class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Ordenar por</label>
                    <select wire:model.live="orden" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="fecha_desc">Fecha (más recientes)</option>
                        <option value="fecha_asc">Fecha (más antiguas)</option>
                        <option value="calificacion_desc">Mejor calificación</option>
                        <option value="calificacion_asc">Peor calificación</option>
                    </select>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <div>
                <h3 class="text-sm font-bold text-neutral-800">Opiniones del sistema</h3>
                <p class="text-xs text-neutral-500">
                    @if ($this->listado['total'] === 0)
                        No hay reseñas con estos filtros.
                    @else
                        Mostrando {{ $this->listado['from'] }}–{{ $this->listado['to'] }} de {{ number_format($this->listado['total']) }}
                    @endif
                </p>
            </div>
            <div wire:loading class="text-xs text-[#D81B60] font-semibold">Actualizando…</div>
        </div>

        @forelse ($this->listado['items'] as $item)
        <article class="bg-white rounded-2xl border border-neutral-100 shadow-sm p-5 hover:border-[#D81B60]/25 transition">
            <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                <div class="flex-1 min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-amber-500 text-sm font-bold tracking-tight" title="{{ $item['calificacion'] }} de 5">
                            {{ $estrellasTexto($item['calificacion']) }}
                        </span>
                        <span class="text-xs font-semibold text-neutral-500">{{ $item['calificacion'] }}/5</span>
                        @if ($item['calificacion'] <= 2)
                        <span class="text-[10px] font-bold uppercase tracking-wide text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full">Baja calificación</span>
                        @endif
                    </div>

                    @if ($item['comentario'] !== '')
                    <p class="text-sm text-neutral-800 leading-relaxed">{{ $item['comentario'] }}</p>
                    @else
                    <p class="text-sm text-neutral-400 italic">Sin comentario escrito (solo calificación).</p>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs text-neutral-500 pt-1">
                        <div><span class="font-semibold text-neutral-600">Autor:</span> {{ $item['autor'] }}</div>
                        <div><span class="font-semibold text-neutral-600">Fecha:</span> {{ $item['fecha'] }}</div>
                        <div class="sm:col-span-2"><span class="font-semibold text-neutral-600">Prenda:</span> {{ $item['producto'] }}</div>
                        <div><span class="font-semibold text-neutral-600">Tienda:</span> {{ $item['tienda'] }}</div>
                        <div><span class="font-semibold text-neutral-600">Vendedor:</span> {{ $item['vendedor'] }}</div>
                        <div><span class="font-semibold text-neutral-600">Artesano:</span> {{ $item['artesano'] }}</div>
                        @if (!empty($item['compra_principal']))
                        <div class="sm:col-span-2">
                            <span class="font-semibold text-neutral-600">Compra relacionada:</span>
                            {{ $item['compra_principal']['referencia'] }}
                            · {{ $item['compra_principal']['fecha'] }}
                            · {{ $item['compra_principal']['estado'] }}
                            · ${{ number_format($item['compra_principal']['total'], 2) }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex lg:flex-col gap-2 shrink-0">
                    <button type="button" wire:click="abrirDetalle({{ $item['id'] }})"
                        class="text-xs font-bold px-4 py-2 rounded-full bg-[#D81B60] text-white hover:bg-[#C2185B]">
                        Ver contexto
                    </button>
                </div>
            </div>
        </article>
        @empty
        <div class="bg-white rounded-2xl border border-neutral-100 px-5 py-16 text-center text-neutral-500">
            <div class="font-semibold text-neutral-700 mb-1">Sin reseñas para mostrar</div>
            <div class="text-sm">Prueba otros filtros o revisa un periodo más amplio.</div>
        </div>
        @endforelse

        @if ($this->listado['totalPages'] > 1)
        <div class="px-1 py-2 flex items-center justify-between gap-3">
            <button type="button"
                wire:click="irAPagina({{ max(1, $this->listado['page'] - 1) }})"
                @disabled($this->listado['page'] <= 1)
                class="text-xs font-semibold px-3 py-2 rounded-full border border-neutral-200 bg-white disabled:opacity-40">
                Anterior
            </button>
            <span class="text-xs text-neutral-500">Página {{ $this->listado['page'] }} de {{ $this->listado['totalPages'] }}</span>
            <button type="button"
                wire:click="irAPagina({{ min($this->listado['totalPages'], $this->listado['page'] + 1) }})"
                @disabled($this->listado['page'] >= $this->listado['totalPages'])
                class="text-xs font-semibold px-3 py-2 rounded-full border border-neutral-200 bg-white disabled:opacity-40">
                Siguiente
            </button>
        </div>
        @endif
    </div>

    @if ($this->detalle)
    <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/40" wire:click="cerrarDetalle"></div>
        <div class="relative w-full max-w-lg h-full bg-white shadow-2xl overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-neutral-100 px-5 py-4 flex items-start justify-between gap-3 z-10">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wide text-[#D81B60]">Detalle de reseña</div>
                    <h3 class="text-lg font-bold text-neutral-900">{{ $this->detalle['producto'] }}</h3>
                    <p class="text-xs text-neutral-500">{{ $this->detalle['fecha'] }}</p>
                </div>
                <button type="button" wire:click="cerrarDetalle" class="p-2 rounded-full hover:bg-neutral-100 text-neutral-500">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-5">
                <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4">
                    <div class="text-amber-600 font-bold text-lg">{{ $estrellasTexto($this->detalle['calificacion']) }}</div>
                    <div class="text-sm text-amber-800 font-semibold mt-1">{{ $this->detalle['calificacion'] }} de 5 estrellas</div>
                    @if ($this->detalle['comentario'] !== '')
                    <p class="text-sm text-neutral-800 mt-3 leading-relaxed">{{ $this->detalle['comentario'] }}</p>
                    @else
                    <p class="text-sm text-neutral-500 mt-3 italic">Sin comentario escrito.</p>
                    @endif
                </div>

                <section class="rounded-2xl bg-[#F8F5F2] p-4 space-y-1">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500">Quién opinó</h4>
                    <p class="text-sm font-semibold text-neutral-900">{{ $this->detalle['autor'] }}</p>
                    @if (!empty($this->detalle['autor_email']))
                    <p class="text-xs text-neutral-600">{{ $this->detalle['autor_email'] }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-neutral-100 p-4 space-y-1">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500">Sobre la prenda</h4>
                    <p class="text-sm font-semibold text-neutral-900">{{ $this->detalle['producto'] }}</p>
                    <p class="text-xs text-neutral-600">Tienda: {{ $this->detalle['tienda'] }}</p>
                    <p class="text-xs text-neutral-600">Vendedor: {{ $this->detalle['vendedor'] }}</p>
                    <p class="text-xs text-neutral-600">Artesano: {{ $this->detalle['artesano'] }}</p>
                </section>

                <section>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500 mb-2">Compras relacionadas</h4>
                    @forelse ($this->detalle['compras'] as $c)
                    <div class="rounded-xl border border-neutral-100 px-4 py-3 mb-2 flex justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-neutral-800">{{ $c['referencia'] }}</div>
                            <div class="text-xs text-neutral-500">{{ $c['fecha'] }} · {{ $c['estado'] }}</div>
                        </div>
                        <div class="text-sm font-bold text-[#D81B60]">${{ number_format($c['total'], 2) }}</div>
                    </div>
                    @empty
                    <p class="text-sm text-neutral-500">No se encontró una compra del mismo cliente con esta prenda. La reseña puede ser de una compra anterior o de otro contexto.</p>
                    @endforelse
                </section>

                @if (!empty($this->detalle['compra_principal']['id']))
                <a href="{{ route('admin.ventas.index') }}"
                    class="inline-flex items-center justify-center w-full text-sm font-bold px-4 py-3 rounded-full border border-[#D81B60] text-[#D81B60] hover:bg-[#D81B60]/5"
                    wire:navigate>
                    Ir a ventas generales
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
