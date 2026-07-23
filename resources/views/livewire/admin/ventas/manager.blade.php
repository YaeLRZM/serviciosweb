<?php

use App\Services\Admin\AdminVentaAccionesService;
use App\Services\Admin\VentasGeneralesDataService;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\{state, computed, mount};

state([
    'busqueda' => '',
    'fecha_desde' => '',
    'fecha_hasta' => '',
    'estado' => '',
    'tienda_id' => '',
    'vendedor_id' => '',
    'artesano_id' => '',
    'cliente_id' => '',
    'monto_min' => '',
    'monto_max' => '',
    'metodo_pago' => '',
    'resenada' => '',
    'en_proceso' => '',
    /** Filtro rápido de los cuadros: ventas|entregadas|en_proceso|canceladas|devoluciones|monto|'' */
    'grupo' => '',
    'orden' => 'fecha_desc',
    'page' => 1,
    'detalleId' => null,
    'error' => null,
    'mostrarFiltros' => true,
    // Acciones admin (modales de confirmación)
    'modalAccion' => null, // cancelar | devolver
    'modalVentaId' => null,
    'accionMensaje' => null,
    'accionError' => null,
]);

// Filtros desde la URL (p. ej. desde alertas del dashboard).
mount(function () {
    $this->busqueda = (string) request('busqueda', '');
    $this->fecha_desde = (string) request('fecha_desde', '');
    $this->fecha_hasta = (string) request('fecha_hasta', '');
    $this->estado = (string) request('estado', '');
    $this->tienda_id = (string) request('tienda_id', '');
    $this->vendedor_id = (string) request('vendedor_id', '');
    $this->artesano_id = (string) request('artesano_id', '');
    $this->cliente_id = (string) request('cliente_id', '');
    $this->monto_min = (string) request('monto_min', '');
    $this->monto_max = (string) request('monto_max', '');
    $this->metodo_pago = (string) request('metodo_pago', '');
    $this->resenada = (string) request('resenada', request('reseñada', ''));
    $this->en_proceso = (string) request('en_proceso', '');
    $grupo = (string) request('grupo', '');
    $this->grupo = in_array($grupo, ['ventas', 'entregadas', 'en_proceso', 'canceladas', 'devoluciones', 'monto'], true)
        ? $grupo
        : '';
    $orden = (string) request('orden', 'fecha_desc');
    $this->orden = in_array($orden, ['fecha_desc', 'fecha_asc', 'monto_desc', 'monto_asc', 'estado'], true)
        ? $orden
        : 'fecha_desc';
    if ($this->estado !== '' || $this->fecha_desde !== '' || $this->en_proceso !== '') {
        $this->mostrarFiltros = true;
    }
});

$filtrosActivos = computed(function () {
    return array_filter([
        'busqueda' => $this->busqueda !== '' ? $this->busqueda : null,
        'fecha_desde' => $this->fecha_desde !== '' ? $this->fecha_desde : null,
        'fecha_hasta' => $this->fecha_hasta !== '' ? $this->fecha_hasta : null,
        'estado' => $this->estado !== '' ? $this->estado : null,
        'en_proceso' => $this->en_proceso !== '' ? $this->en_proceso : null,
        'grupo' => $this->grupo !== '' ? $this->grupo : null,
        'tienda_id' => $this->tienda_id !== '' ? $this->tienda_id : null,
        'vendedor_id' => $this->vendedor_id !== '' ? $this->vendedor_id : null,
        'artesano_id' => $this->artesano_id !== '' ? $this->artesano_id : null,
        'cliente_id' => $this->cliente_id !== '' ? $this->cliente_id : null,
        'monto_min' => $this->monto_min !== '' ? $this->monto_min : null,
        'monto_max' => $this->monto_max !== '' ? $this->monto_max : null,
        'metodo_pago' => $this->metodo_pago !== '' ? $this->metodo_pago : null,
        'reseñada' => $this->resenada !== '' ? $this->resenada : null,
        'orden' => $this->orden ?: 'fecha_desc',
    ], fn ($v) => $v !== null && $v !== '');
});

$opciones = computed(function () {
    try {
        return app(VentasGeneralesDataService::class)->opcionesFiltro();
    } catch (\Throwable $e) {
        return [
            'tiendas' => [],
            'vendedores' => [],
            'artesanos' => [],
            'clientes' => [],
            'estados' => [],
        ];
    }
});

$resumen = computed(function () {
    try {
        $this->error = null;

        return app(VentasGeneralesDataService::class)->resumen($this->filtrosActivos);
    } catch (\Throwable $e) {
        $this->error = 'No se pudo calcular el resumen de ventas.';

        return [
            'total_compras' => 0,
            'ventas' => 0,
            'monto_total' => 0,
            'entregadas' => 0,
            'canceladas' => 0,
            'devoluciones' => 0,
            'devueltas' => 0,
            'en_proceso' => 0,
            'top_vendedores' => [],
        ];
    }
});

$listado = computed(function () {
    try {
        $this->error = null;
        $svc = app(VentasGeneralesDataService::class);
        $page = max(1, (int) $this->page);
        $query = $svc->aplicarOrden(
            $svc->baseQuery($this->filtrosActivos),
            (string) ($this->filtrosActivos['orden'] ?? 'fecha_desc')
        );
        $total = (clone $query)->count();
        $perPage = 12;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);
        $rows = $query->forPage($page, $perPage)->get();

        return [
            'items' => $svc->mapearColeccion($rows),
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ];
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error(
            'Error cargando listado de ventas admin: '.$e->getMessage(),
            ['exception' => $e::class, 'file' => $e->getFile(), 'line' => $e->getLine()]
        );
        $this->error = 'No se pudieron cargar las ventas. Intenta de nuevo.';

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
        return app(VentasGeneralesDataService::class)->detalle((int) $this->detalleId);
    } catch (\Throwable $e) {
        return null;
    }
});

$irAPagina = function ($p) {
    $this->page = max(1, (int) $p);
};

// Al cambiar filtros (excepto página y panel), volver al inicio del listado.
$updatedBusqueda = fn () => $this->page = 1;
$updatedFecha_desde = fn () => $this->page = 1;
$updatedFecha_hasta = fn () => $this->page = 1;
$updatedTienda_id = fn () => $this->page = 1;
$updatedVendedor_id = fn () => $this->page = 1;
$updatedArtesano_id = fn () => $this->page = 1;
$updatedCliente_id = fn () => $this->page = 1;
$updatedMonto_min = fn () => $this->page = 1;
$updatedMonto_max = fn () => $this->page = 1;
$updatedMetodo_pago = fn () => $this->page = 1;
$updatedResenada = fn () => $this->page = 1;
$updatedOrden = fn () => $this->page = 1;
$updatedGrupo = fn () => $this->page = 1;

// Si el usuario cambia el estado manualmente, no mezclar con el cuadro rápido.
$updatedEstado = function () {
    $this->page = 1;
    if ($this->estado !== '') {
        $this->grupo = '';
        $this->en_proceso = '';
    }
};

$abrirDetalle = function ($id) {
    $this->detalleId = (int) $id;
    $this->accionMensaje = null;
    $this->accionError = null;
};

$cerrarDetalle = function () {
    $this->detalleId = null;
    $this->modalAccion = null;
    $this->modalVentaId = null;
    $this->accionError = null;
};

$pedirCancelar = function ($id) {
    $this->modalVentaId = (int) $id;
    $this->modalAccion = 'cancelar';
    $this->accionError = null;
};

$pedirDevolver = function ($id) {
    $this->modalVentaId = (int) $id;
    $this->modalAccion = 'devolver';
    $this->accionError = null;
};

$cerrarModalAccion = function () {
    $this->modalAccion = null;
    $this->modalVentaId = null;
    $this->accionError = null;
};

$confirmarCancelar = function () {
    $this->accionError = null;
    $admin = Auth::user();
    if (! $admin || ! $admin->hasRole('admin')) {
        $this->accionError = 'No tienes permiso para esta acción.';

        return;
    }
    try {
        $venta = app(AdminVentaAccionesService::class)
            ->cancelar($admin, (int) $this->modalVentaId);
        $this->modalAccion = null;
        $this->modalVentaId = null;
        $this->detalleId = (int) $venta->id;
        $this->accionMensaje = 'La venta fue cancelada correctamente.';
    } catch (\InvalidArgumentException $e) {
        $this->accionError = $e->getMessage();
    } catch (\Throwable $e) {
        $this->accionError = 'No se pudo cancelar la venta. Intenta de nuevo.';
    }
};

$confirmarDevolver = function () {
    $this->accionError = null;
    $admin = Auth::user();
    if (! $admin || ! $admin->hasRole('admin')) {
        $this->accionError = 'No tienes permiso para esta acción.';

        return;
    }
    try {
        $venta = app(AdminVentaAccionesService::class)
            ->iniciarDevolucion($admin, (int) $this->modalVentaId);
        $this->modalAccion = null;
        $this->modalVentaId = null;
        $this->detalleId = (int) $venta->id;
        $this->accionMensaje = 'La devolución ha comenzado. En unos 2 minutos pasará a “Devuelto”.';
    } catch (\InvalidArgumentException $e) {
        $this->accionError = $e->getMessage();
    } catch (\Throwable $e) {
        $this->accionError = 'No se pudo iniciar la devolución. Intenta de nuevo.';
    }
};

$limpiarFiltros = function () {
    $this->busqueda = '';
    $this->fecha_desde = '';
    $this->fecha_hasta = '';
    $this->estado = '';
    $this->tienda_id = '';
    $this->vendedor_id = '';
    $this->artesano_id = '';
    $this->cliente_id = '';
    $this->monto_min = '';
    $this->monto_max = '';
    $this->metodo_pago = '';
    $this->resenada = '';
    $this->en_proceso = '';
    $this->grupo = '';
    $this->orden = 'fecha_desc';
    $this->page = 1;
};

/** Clic en cuadro superior: aplica filtro de grupo (toggle si ya está activo). */
$filtrarPorCuadro = function (string $grupo) {
    $permitidos = ['ventas', 'entregadas', 'en_proceso', 'canceladas', 'devoluciones', 'monto'];
    if (! in_array($grupo, $permitidos, true)) {
        return;
    }
    if ($this->grupo === $grupo) {
        $this->grupo = '';
    } else {
        $this->grupo = $grupo;
        // Evitar conflicto con filtros de estado sueltos.
        $this->estado = '';
        $this->en_proceso = '';
    }
    $this->page = 1;
};

?>
@php
    $badgeEstadoClases = function (string $estado): string {
        return match (strtolower($estado)) {
            'entregado', 'completada' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'cancelada' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'devolucion_en_proceso' => 'bg-violet-50 text-violet-800 ring-violet-200',
            'devuelto' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
            'en_curso', 'pago_acreditado', 'listo_pagar' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'pendiente', 'pendiente_activacion' => 'bg-sky-50 text-sky-700 ring-sky-200',
            default => 'bg-neutral-100 text-neutral-600 ring-neutral-200',
        };
    };
@endphp

<div class="space-y-6">
    @if ($error)
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl font-medium">
        {{ $error }}
    </div>
    @endif

    {{-- Resumen: clic = filtro rápido; Limpiar filtros quita el cuadro activo --}}
    @if ($grupo !== '')
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-[#D81B60]/20 bg-[#D81B60]/5 px-4 py-2.5">
        <p class="text-xs text-neutral-700">
            <span class="font-bold text-[#D81B60]">Filtro del cuadro activo:</span>
            {{ match ($grupo) {
                'ventas' => 'Ventas (sin canceladas ni devoluciones)',
                'monto' => 'Monto total (sin canceladas ni devueltas)',
                'entregadas' => 'Entregadas',
                'en_proceso' => 'En proceso',
                'canceladas' => 'Canceladas',
                'devoluciones' => 'Devoluciones (en proceso y devueltas)',
                default => 'Filtro rápido',
            } }}
        </p>
        <button type="button" wire:click="limpiarFiltros"
            class="text-xs font-bold text-[#D81B60] hover:underline">
            Limpiar filtros
        </button>
    </div>
    @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-4">
        <x-admin.stat-mini-card
            label="Ventas"
            :value="number_format($this->resumen['ventas'] ?? 0)"
            trend="Sin canceladas ni devoluciones"
            :clickable="true"
            :active="$grupo === 'ventas'"
            wire:click="filtrarPorCuadro('ventas')"
            icon-bg="bg-[#D81B60]/10"
            icon-color="text-[#D81B60]"
            border-color="border-[#D81B60]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M7 13l-2-8m5 14a1 1 0 11-2 0 1 1 0 012 0zm8 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Monto total"
            :value="'$'.number_format($this->resumen['monto_total'], 2)"
            trend="Sin canceladas ni devueltas"
            trend-color="text-emerald-600"
            :clickable="true"
            :active="$grupo === 'monto'"
            wire:click="filtrarPorCuadro('monto')"
            icon-bg="bg-emerald-100"
            icon-color="text-emerald-600"
            border-color="border-emerald-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v-1"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Entregadas"
            :value="number_format($this->resumen['entregadas'])"
            trend="Completadas con éxito"
            trend-color="text-emerald-600"
            :clickable="true"
            :active="$grupo === 'entregadas'"
            wire:click="filtrarPorCuadro('entregadas')"
            icon-bg="bg-emerald-50"
            icon-color="text-emerald-600"
            border-color="border-emerald-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="En proceso"
            :value="number_format($this->resumen['en_proceso'])"
            trend="Aún no finalizan"
            trend-color="text-amber-600"
            :clickable="true"
            :active="$grupo === 'en_proceso'"
            wire:click="filtrarPorCuadro('en_proceso')"
            icon-bg="bg-amber-50"
            icon-color="text-amber-600"
            border-color="border-amber-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Canceladas"
            :value="number_format($this->resumen['canceladas'])"
            trend="Requieren atención"
            trend-color="text-rose-600"
            :clickable="true"
            :active="$grupo === 'canceladas'"
            wire:click="filtrarPorCuadro('canceladas')"
            icon-bg="bg-rose-50"
            icon-color="text-rose-600"
            border-color="border-rose-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Devoluciones"
            :value="number_format($this->resumen['devoluciones'] ?? 0)"
            trend="En proceso y devueltas"
            trend-color="text-violet-700"
            :clickable="true"
            :active="$grupo === 'devoluciones'"
            wire:click="filtrarPorCuadro('devoluciones')"
            icon-bg="bg-violet-50"
            icon-color="text-violet-700"
            border-color="border-violet-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m8 5l3-3m0 0l-3-3m3 3H9"/></svg>
        </x-admin.stat-mini-card>
    </div>

    @if (!empty($this->resumen['top_vendedores']))
    <div class="bg-white rounded-2xl border border-neutral-100 shadow-sm p-5">
        <h3 class="text-sm font-bold text-neutral-800 mb-3">Quién más vende (filtro actual)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach ($this->resumen['top_vendedores'] as $i => $top)
            <div class="flex items-center gap-3 rounded-xl bg-[#F8F5F2] px-4 py-3">
                <span class="w-8 h-8 rounded-full bg-[#D81B60]/10 text-[#D81B60] text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-neutral-800 truncate">{{ $top['nombre'] }}</div>
                    <div class="text-xs text-neutral-500">{{ $top['compras'] }} compra(s) · ${{ number_format($top['monto'], 2) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-neutral-100 shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-5 py-4 border-b border-neutral-100">
            <div>
                <h3 class="text-sm font-bold text-neutral-800">Buscar y filtrar compras</h3>
                <p class="text-xs text-neutral-500 mt-0.5">Usa los filtros para localizar un caso o revisar un periodo.</p>
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
                    placeholder="Buscar por número de compra, cliente, tienda o prenda..."
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
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Estado</label>
                    <select wire:model.live="estado" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todos</option>
                        @foreach ($this->opciones['estados'] as $e)
                        <option value="{{ $e['id'] }}">{{ $e['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Método de pago</label>
                    <select wire:model.live="metodo_pago" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todos</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="efectivo">Efectivo</option>
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
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Monto mínimo</label>
                    <input type="number" step="0.01" min="0" wire:model.live.debounce.400ms="monto_min" placeholder="0.00"
                        class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Monto máximo</label>
                    <input type="number" step="0.01" min="0" wire:model.live.debounce.400ms="monto_max" placeholder="0.00"
                        class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Con reseña del cliente</label>
                    <select wire:model.live="resenada" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="">Todas</option>
                        <option value="si">Sí tienen reseña</option>
                        <option value="no">Aún sin reseña</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wide text-neutral-400">Ordenar por</label>
                    <select wire:model.live="orden" class="mt-1 w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                        <option value="fecha_desc">Fecha (más recientes)</option>
                        <option value="fecha_asc">Fecha (más antiguas)</option>
                        <option value="monto_desc">Monto (mayor a menor)</option>
                        <option value="monto_asc">Monto (menor a mayor)</option>
                        <option value="estado">Estado</option>
                    </select>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-neutral-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-neutral-800">Listado de compras</h3>
                <p class="text-xs text-neutral-500">
                    @if ($this->listado['total'] === 0)
                        No hay resultados con estos filtros.
                    @else
                        Mostrando {{ $this->listado['from'] }}–{{ $this->listado['to'] }} de {{ number_format($this->listado['total']) }}
                    @endif
                </p>
            </div>
            <div wire:loading class="text-xs text-[#D81B60] font-semibold">Actualizando…</div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[960px]">
                <thead>
                    <tr class="bg-neutral-50 text-[11px] font-bold uppercase tracking-widest text-neutral-400">
                        <th class="px-4 py-3 border-b border-neutral-100">Compra</th>
                        <th class="px-4 py-3 border-b border-neutral-100">Cliente</th>
                        <th class="px-4 py-3 border-b border-neutral-100">Vendedor / Tienda</th>
                        <th class="px-4 py-3 border-b border-neutral-100">Prenda(s)</th>
                        <th class="px-4 py-3 border-b border-neutral-100">Total</th>
                        <th class="px-4 py-3 border-b border-neutral-100">Estado</th>
                        <th class="px-4 py-3 border-b border-neutral-100">Pago</th>
                        <th class="px-4 py-3 border-b border-neutral-100 text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse ($this->listado['items'] as $item)
                    <tr class="hover:bg-[#F8F5F2]/60 transition {{ $detalleId == $item['id'] ? 'bg-[#D81B60]/5' : '' }}">
                        <td class="px-4 py-3 align-top">
                            <div class="font-bold text-neutral-800">{{ $item['referencia'] }}</div>
                            <div class="text-xs text-neutral-500">{{ $item['fecha'] }}</div>
                            @if ($item['tiene_resena'])
                            <span class="inline-flex mt-1 text-[10px] font-bold uppercase tracking-wide text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Con reseña</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="font-medium text-neutral-800">{{ $item['cliente'] }}</div>
                            <div class="text-xs text-neutral-500 truncate max-w-[160px]">{{ $item['cliente_email'] }}</div>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="font-medium text-neutral-800">{{ $item['vendedor'] }}</div>
                            <div class="text-xs text-neutral-500">{{ $item['tienda'] }}</div>
                            @if ($item['artesanos'] !== '—')
                            <div class="text-[11px] text-neutral-400 mt-0.5">Artesano: {{ $item['artesanos'] }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="text-neutral-800 line-clamp-2 max-w-[220px]">{{ $item['productos_resumen'] }}</div>
                            <div class="text-xs text-neutral-500 mt-0.5">{{ $item['cantidad_total'] }} uds · {{ $item['lineas'] }} línea(s)</div>
                        </td>
                        <td class="px-4 py-3 align-top font-bold text-[#D81B60] whitespace-nowrap">
                            ${{ number_format($item['total'], 2) }}
                        </td>
                        <td class="px-4 py-3 align-top">
                            <span class="inline-flex text-[11px] font-semibold px-2.5 py-1 rounded-full ring-1 ring-inset {{ $badgeEstadoClases($item['estado']) }}">
                                {{ $item['estado_etiqueta'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-top text-neutral-600 text-xs">
                            {{ $item['metodo'] }}
                        </td>
                        <td class="px-4 py-3 align-top text-right">
                            <button type="button" wire:click="abrirDetalle({{ $item['id'] }})"
                                class="text-xs font-bold text-[#D81B60] hover:underline">
                                Revisar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-neutral-500">
                            <div class="font-semibold text-neutral-700 mb-1">Sin compras para mostrar</div>
                            <div class="text-sm">Prueba ampliando el rango de fechas o quitando algunos filtros.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->listado['totalPages'] > 1)
        <div class="px-5 py-4 border-t border-neutral-100 flex items-center justify-between gap-3">
            <button type="button"
                wire:click="irAPagina({{ max(1, $this->listado['page'] - 1) }})"
                @disabled($this->listado['page'] <= 1)
                class="text-xs font-semibold px-3 py-2 rounded-full border border-neutral-200 disabled:opacity-40">
                Anterior
            </button>
            <span class="text-xs text-neutral-500">Página {{ $this->listado['page'] }} de {{ $this->listado['totalPages'] }}</span>
            <button type="button"
                wire:click="irAPagina({{ min($this->listado['totalPages'], $this->listado['page'] + 1) }})"
                @disabled($this->listado['page'] >= $this->listado['totalPages'])
                class="text-xs font-semibold px-3 py-2 rounded-full border border-neutral-200 disabled:opacity-40">
                Siguiente
            </button>
        </div>
        @endif
    </div>

    {{-- Panel detalle --}}
    @if ($this->detalle)
    <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/40" wire:click="cerrarDetalle"></div>
        <div class="relative w-full max-w-lg h-full bg-white shadow-2xl overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-neutral-100 px-5 py-4 flex items-start justify-between gap-3 z-10">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wide text-[#D81B60]">Detalle de compra</div>
                    <h3 class="text-lg font-bold text-neutral-900">{{ $this->detalle['referencia'] }}</h3>
                    <p class="text-xs text-neutral-500">{{ $this->detalle['fecha'] }}</p>
                </div>
                <button type="button" wire:click="cerrarDetalle" class="p-2 rounded-full hover:bg-neutral-100 text-neutral-500">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-5">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex text-[11px] font-semibold px-2.5 py-1 rounded-full ring-1 ring-inset {{ $badgeEstadoClases($this->detalle['estado']) }}">
                        {{ $this->detalle['estado_etiqueta'] }}
                    </span>
                    <span class="inline-flex text-[11px] font-semibold px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-600">
                        {{ $this->detalle['metodo'] }}
                    </span>
                    <span class="inline-flex text-[11px] font-bold px-2.5 py-1 rounded-full bg-[#D81B60]/10 text-[#D81B60]">
                        ${{ number_format($this->detalle['total'], 2) }}
                    </span>
                </div>

                @if ($accionMensaje)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ $accionMensaje }}
                </div>
                @endif

                {{-- Contador real de devolución (2 min) — mismo next_state_at que API móvil --}}
                @if (($this->detalle['estado'] ?? '') === 'devolucion_en_proceso' && !empty($this->detalle['next_state_at_iso']))
                <div wire:poll.10s>
                    <div
                        class="rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3"
                        x-data="{
                            endMs: new Date(@js($this->detalle['next_state_at_iso'])).getTime(),
                            clock: '00:00',
                            finished: false,
                            tick() {
                                const left = Math.max(0, Math.floor((this.endMs - Date.now()) / 1000));
                                const m = Math.floor(left / 60);
                                const s = left % 60;
                                this.clock = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                                this.finished = left === 0;
                            }
                        }"
                        x-init="tick(); setInterval(() => tick(), 1000)"
                    >
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full bg-violet-100 text-violet-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-violet-900">La devolución está en proceso</p>
                                <p class="text-xs text-violet-800/90 mt-0.5" x-show="!finished">
                                    Tiempo restante de devolución
                                </p>
                                <p class="text-xs text-violet-800/90 mt-0.5" x-show="finished" x-cloak>
                                    Completando devolución…
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-2xl font-black text-violet-800 tabular-nums tracking-tight" x-text="clock">00:00</div>
                                <div class="text-[10px] font-semibold uppercase text-violet-600">Falta</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Acciones solo administrador (esta vista ya está tras role:admin) --}}
                <section class="rounded-2xl border border-neutral-100 p-4 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500">Acciones de administración</h4>
                    <p class="text-xs text-neutral-500">Estas acciones son delicadas y piden confirmación.</p>
                    <div class="flex flex-wrap gap-2">
                        @if ($this->detalle['puede_cancelar'] ?? false)
                        <button type="button" wire:click="pedirCancelar({{ $this->detalle['id'] }})"
                            class="inline-flex items-center rounded-full border border-rose-300 bg-white px-4 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50 transition">
                            Cancelar venta
                        </button>
                        @endif
                        @if ($this->detalle['puede_devolver'] ?? false)
                        <button type="button" wire:click="pedirDevolver({{ $this->detalle['id'] }})"
                            class="inline-flex items-center rounded-full border border-violet-300 bg-white px-4 py-2 text-xs font-bold text-violet-800 hover:bg-violet-50 transition">
                            Iniciar devolución
                        </button>
                        @endif
                        @if (!($this->detalle['puede_cancelar'] ?? false) && !($this->detalle['puede_devolver'] ?? false))
                        <p class="text-sm text-neutral-500">
                            @if (($this->detalle['estado'] ?? '') === 'cancelada')
                                Esta venta ya está cancelada.
                            @elseif (($this->detalle['estado'] ?? '') === 'devolucion_en_proceso')
                                La devolución está en proceso. El contador de arriba muestra el tiempo restante.
                            @elseif (($this->detalle['estado'] ?? '') === 'devuelto')
                                Esta venta ya fue devuelta.
                            @else
                                No hay acciones disponibles en este estado.
                            @endif
                        </p>
                        @endif
                    </div>
                    @if (!empty($this->detalle['admin_nota']))
                    <div class="rounded-xl bg-[#F8F5F2] px-3 py-2 text-xs text-neutral-700">
                        <span class="font-semibold">Registro de administración:</span>
                        {{ $this->detalle['admin_nota'] }}
                        @if (!empty($this->detalle['admin_accion_at']))
                            <span class="text-neutral-500"> · {{ $this->detalle['admin_accion_at'] }}</span>
                        @endif
                        @if (!empty($this->detalle['admin_nombre']))
                            <span class="text-neutral-500"> · {{ $this->detalle['admin_nombre'] }}</span>
                        @endif
                    </div>
                    @endif
                </section>

                <section class="rounded-2xl bg-[#F8F5F2] p-4 space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500">Quién compró</h4>
                    <p class="text-sm font-semibold text-neutral-900">{{ $this->detalle['cliente'] }}</p>
                    @if (!empty($this->detalle['cliente_email']))
                    <p class="text-xs text-neutral-600">{{ $this->detalle['cliente_email'] }}</p>
                    @endif
                    @if (!empty($this->detalle['cliente_telefono']))
                    <p class="text-xs text-neutral-600">Tel. {{ $this->detalle['cliente_telefono'] }}</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-neutral-100 p-4 space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500">Quién vendió</h4>
                    <p class="text-sm font-semibold text-neutral-900">{{ $this->detalle['vendedor'] }}</p>
                    <p class="text-xs text-neutral-600">Tienda: {{ $this->detalle['tienda'] }}</p>
                    @if ($this->detalle['artesanos'] !== '—')
                    <p class="text-xs text-neutral-600">Artesano: {{ $this->detalle['artesanos'] }}</p>
                    @endif
                </section>

                <section>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500 mb-2">Prendas de la compra</h4>
                    <div class="space-y-2">
                        @foreach ($this->detalle['productos'] as $p)
                        <div class="rounded-xl border border-neutral-100 px-4 py-3 flex justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-neutral-800">{{ $p['nombre'] }}</div>
                                @if (!empty($p['artesano']))
                                <div class="text-xs text-neutral-500">{{ $p['artesano'] }}</div>
                                @endif
                                <div class="text-xs text-neutral-500">Cantidad: {{ $p['cantidad'] }} · ${{ number_format($p['precio_unitario'], 2) }} c/u</div>
                            </div>
                            <div class="text-sm font-bold text-[#D81B60] whitespace-nowrap">${{ number_format($p['subtotal'], 2) }}</div>
                        </div>
                        @endforeach
                    </div>
                </section>

                @if (!empty($this->detalle['codigo_barras']))
                <section class="rounded-xl bg-neutral-50 px-4 py-3">
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500">Código de seguimiento</h4>
                    <p class="text-sm font-mono text-neutral-800 mt-1">{{ $this->detalle['codigo_barras'] }}</p>
                </section>
                @endif

                <section>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-neutral-500 mb-2">Reseñas relacionadas</h4>
                    @forelse ($this->detalle['resenas'] as $r)
                    <div class="rounded-xl border border-neutral-100 px-4 py-3 mb-2 {{ $r['del_comprador'] ? 'bg-amber-50/40 border-amber-100' : '' }}">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-neutral-800">{{ $r['autor'] }}</div>
                            <div class="text-xs font-bold text-amber-600">{{ $r['calificacion'] }}/5</div>
                        </div>
                        <div class="text-[11px] text-neutral-500 mt-0.5">{{ $r['producto'] }} · {{ $r['fecha'] }}</div>
                        @if ($r['del_comprador'])
                        <span class="inline-flex mt-1 text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">Del comprador de esta compra</span>
                        @endif
                        @if ($r['comentario'] !== '')
                        <p class="text-sm text-neutral-700 mt-2 leading-relaxed">{{ $r['comentario'] }}</p>
                        @else
                        <p class="text-sm text-neutral-400 mt-2 italic">Sin comentario escrito.</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-sm text-neutral-500">No hay reseñas ligadas a las prendas de esta compra.</p>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal confirmación cancelar / devolver --}}
    @if ($modalAccion && $modalVentaId)
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-neutral-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-neutral-100 {{ $modalAccion === 'cancelar' ? 'bg-rose-50' : 'bg-violet-50' }}">
                <h3 class="text-lg font-bold {{ $modalAccion === 'cancelar' ? 'text-rose-900' : 'text-violet-900' }}">
                    {{ $modalAccion === 'cancelar' ? 'Cancelar venta' : 'Iniciar devolución' }}
                </h3>
                <p class="text-sm mt-1 {{ $modalAccion === 'cancelar' ? 'text-rose-800/90' : 'text-violet-800/90' }}">
                    Esta acción requiere confirmación
                </p>
            </div>
            <div class="px-6 py-5 space-y-4">
                @if ($modalAccion === 'cancelar')
                <p class="text-sm text-neutral-700 leading-relaxed">
                    Vas a cancelar la compra <span class="font-bold">CMP-{{ str_pad((string) $modalVentaId, 5, '0', STR_PAD_LEFT) }}</span>.
                    El stock de las prendas se devolverá al catálogo. No se puede deshacer con un solo clic.
                </p>
                @else
                <p class="text-sm text-neutral-700 leading-relaxed">
                    Vas a iniciar la devolución de dinero de
                    <span class="font-bold">CMP-{{ str_pad((string) $modalVentaId, 5, '0', STR_PAD_LEFT) }}</span>.
                    Primero quedará en <strong>devolución en proceso</strong> y, tras unos 2 minutos, pasará a <strong>devuelto</strong>.
                </p>
                @endif

                @if ($accionError)
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 font-medium">
                    {{ $accionError }}
                </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="cerrarModalAccion"
                        class="rounded-full px-4 py-2 text-sm font-semibold text-neutral-600 hover:bg-neutral-100">
                        No, volver
                    </button>
                    @if ($modalAccion === 'cancelar')
                    <button type="button" wire:click="confirmarCancelar"
                        class="rounded-full bg-rose-700 px-5 py-2 text-sm font-bold text-white hover:bg-rose-800">
                        Sí, cancelar venta
                    </button>
                    @else
                    <button type="button" wire:click="confirmarDevolver"
                        class="rounded-full bg-violet-700 px-5 py-2 text-sm font-bold text-white hover:bg-violet-800">
                        Sí, iniciar devolución
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
