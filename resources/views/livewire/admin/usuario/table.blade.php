<?php

use function Livewire\Volt\{state, computed};

state([
    'rol' => 'Todos',
    'estatus' => 'Todos',
    'busqueda' => '',
    'page' => 1,
]);

$roles = computed(fn() => ['Todos', 'Customer', 'Buyer', 'Artisan']);
$estatuses = computed(fn() => ['Todos', 'Active', 'Suspended', 'Flagged']);

$rolBadges = computed(fn() => [
    'Customer' => 'bg-neutral-100 text-neutral-600',
    'Buyer'    => 'bg-sky-50 text-sky-700',
    'Artisan'  => 'bg-[#D81B60]/10 text-[#D81B60]',
]);

$estatusBadges = computed(fn() => [
    'Active'    => ['dot' => 'bg-emerald-500', 'text' => 'bg-emerald-50 text-emerald-600'],
    'Suspended' => ['dot' => 'bg-neutral-400', 'text' => 'bg-neutral-100 text-neutral-500'],
    'Flagged'   => ['dot' => 'bg-rose-500', 'text' => 'bg-rose-50 text-rose-600'],
]);

// TODO: reemplazar por User::query()->with('roles')->...
$dataset = computed(fn() => collect([
    ['id' => 1, 'nombre' => 'Alejandro Ruiz', 'codigo' => 'UA-8219', 'email' => 'a.ruiz@example.mx', 'foto' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200', 'rol' => 'Buyer', 'estatus' => 'Flagged', 'ingreso' => '12 Oct 2023'],
    ['id' => 2, 'nombre' => 'Elena Montes', 'codigo' => 'UA-1044', 'email' => 'elena.m@textiles.com', 'foto' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200', 'rol' => 'Artisan', 'estatus' => 'Active', 'ingreso' => '05 Ene 2024'],
    ['id' => 3, 'nombre' => 'Roberto Sanchez', 'codigo' => 'UA-4590', 'email' => 'rs.92@webmail.mx', 'foto' => null, 'rol' => 'Customer', 'estatus' => 'Suspended', 'ingreso' => '20 Dic 2023'],
    ['id' => 4, 'nombre' => 'Julian Cordoba', 'codigo' => 'UA-9921', 'email' => 'j.cordoba@design.com', 'foto' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=200', 'rol' => 'Customer', 'estatus' => 'Active', 'ingreso' => '11 Feb 2024'],
]));

$filtered = computed(function () {
    $perPage = 10;

    $items = $this->dataset
        ->when($this->rol !== 'Todos', fn($q) => $q->where('rol', $this->rol))
        ->when($this->estatus !== 'Todos', fn($q) => $q->where('estatus', $this->estatus))
        ->when($this->busqueda !== '', fn($q) => $q->filter(
            fn($item) => str_contains(mb_strtolower($item['nombre']), mb_strtolower($this->busqueda))
                || str_contains(mb_strtolower($item['email']), mb_strtolower($this->busqueda))
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
    ];
});

$irAPagina = function ($p) {
    $this->page = $p;
};

$alternarSuspension = function ($id) {
    // TODO: User::find($id)->update(['estatus' => $estatus === 'Suspended' ? 'Active' : 'Suspended']);
    session()->flash('mensaje', 'Estatus del usuario actualizado.');
};

$eliminar = function ($id) {
    // TODO: User::find($id)->delete();
    session()->flash('mensaje', 'Usuario eliminado.');
};
?>

<div class="bg-white rounded-2xl shadow-sm border border-neutral-100 overflow-hidden">

    {{-- Filtros --}}
    <div class="flex flex-col md:flex-row gap-3 justify-between items-center p-5 border-b border-neutral-100 bg-neutral-50/40">
        <div class="flex flex-wrap gap-2">
            <select wire:model.live="rol" class="bg-white border-neutral-200 rounded-full text-sm px-4 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                @foreach ($this->roles as $r)
                <option value="{{ $r }}">{{ $r === 'Todos' ? 'Todos los roles' : $r }}</option>
                @endforeach
            </select>

            <select wire:model.live="estatus" class="bg-white border-neutral-200 rounded-full text-sm px-4 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                @foreach ($this->estatuses as $e)
                <option value="{{ $e }}">{{ $e === 'Todos' ? 'Todos los estatus' : $e }}</option>
                @endforeach
            </select>

            <div class="relative">
                <input
                    wire:model.live.debounce.400ms="busqueda"
                    type="text"
                    placeholder="Buscar por nombre o correo..."
                    class="text-sm rounded-full border-neutral-200 bg-white pl-4 pr-3 py-2 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
            </div>
        </div>

        <p class="text-sm text-neutral-500">Mostrando {{ $this->filtered['items']->count() }} de {{ $this->filtered['total'] }} usuarios</p>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-neutral-50/60 text-[11px] font-bold uppercase tracking-widest text-neutral-400">
                    <th class="px-5 py-4 border-b border-neutral-100">Usuario</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Email</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Rol</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Estatus</th>
                    <th class="px-5 py-4 border-b border-neutral-100">Ingreso</th>
                    <th class="px-5 py-4 border-b border-neutral-100 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($this->filtered['items'] as $item)
                <tr class="hover:bg-neutral-50/60 transition {{ $item['estatus'] === 'Suspended' ? 'opacity-70' : '' }}">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if ($item['foto'])
                            <img src="{{ $item['foto'] }}" class="w-10 h-10 rounded-full object-cover border border-neutral-200" alt="{{ $item['nombre'] }}" />
                            @else
                            <div class="w-10 h-10 rounded-full bg-neutral-100 border border-neutral-200 flex items-center justify-center text-neutral-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            @endif
                            <div>
                                <p class="font-semibold text-neutral-800">{{ $item['nombre'] }}</p>
                                <p class="text-xs text-neutral-400">ID: {{ $item['codigo'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-neutral-600">{{ $item['email'] }}</td>
                    <td class="px-5 py-4">
                        <span class="text-xs font-medium px-3 py-1 rounded-full {{ $this->rolBadges[$item['rol']] ?? 'bg-neutral-100 text-neutral-600' }}">
                            {{ $item['rol'] }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        @php $eb = $this->estatusBadges[$item['estatus']] ?? ['dot' => 'bg-neutral-400', 'text' => 'bg-neutral-100 text-neutral-600']; @endphp
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $eb['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $eb['dot'] }}"></span>
                            {{ $item['estatus'] }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-neutral-500">{{ $item['ingreso'] }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <button
                                wire:click="$dispatch('abrirUsuario', { id: {{ $item['id'] }} })"
                                class="w-8 h-8 rounded-full hover:bg-[#D81B60]/10 flex items-center justify-center text-[#D81B60] transition"
                                title="Ver perfil">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>

                            <button
                                wire:click="$dispatch('abrirUsuario', { id: {{ $item['id'] }}, editar: true })"
                                class="w-8 h-8 rounded-full hover:bg-neutral-100 flex items-center justify-center text-neutral-500 transition"
                                title="Editar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                            </button>

                            @if ($item['estatus'] === 'Suspended')
                            <button
                                wire:click="alternarSuspension({{ $item['id'] }})"
                                class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 transition"
                                title="Reactivar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            @else
                            <button
                                wire:click="alternarSuspension({{ $item['id'] }})"
                                wire:confirm="¿Suspender a este usuario?"
                                class="w-8 h-8 rounded-full hover:bg-rose-50 flex items-center justify-center text-rose-400 hover:text-rose-600 transition"
                                title="Suspender">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM6 6l12 12" />
                                </svg>
                            </button>
                            @endif

                            <button
                                wire:click="eliminar({{ $item['id'] }})"
                                wire:confirm="¿Eliminar permanentemente a este usuario?"
                                class="w-8 h-8 rounded-full hover:bg-rose-50 flex items-center justify-center text-rose-400 hover:text-rose-600 transition"
                                title="Eliminar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-neutral-400 text-sm">
                        No hay usuarios que coincidan con el filtro.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="bg-neutral-50/40 px-5 py-4 border-t border-neutral-100 flex justify-between items-center">
        <button wire:click="irAPagina({{ $this->filtered['page'] - 1 }})" @disabled($this->filtered['page'] <= 1)
                class="px-5 py-2 border border-neutral-200 rounded-full text-sm disabled:opacity-40 hover:bg-white transition">
                Anterior
        </button>

        <div class="flex gap-1">
            @for ($p = 1; $p <= $this->filtered['totalPages']; $p++)
                <button wire:click="irAPagina({{ $p }})"
                    class="w-8 h-8 rounded-full text-sm font-medium transition {{ $p === $this->filtered['page'] ? 'bg-[#D81B60] text-white' : 'hover:bg-neutral-100 text-neutral-600' }}">
                    {{ $p }}
                </button>
                @endfor
        </div>

        <button wire:click="irAPagina({{ $this->filtered['page'] + 1 }})" @disabled($this->filtered['page'] >= $this->filtered['totalPages'])
            class="px-5 py-2 border border-neutral-200 rounded-full text-sm disabled:opacity-40 hover:bg-white transition">
            Siguiente
        </button>
    </div>
</div>