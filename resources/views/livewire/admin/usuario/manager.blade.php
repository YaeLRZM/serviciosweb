<?php

use App\Services\Usuarios\UsuariosDataService;
use Illuminate\Support\Facades\Schema;
use function Livewire\Volt\{state, computed};

// No mostrar métricas de estatus si la columna no existe en PostgreSQL.
$tieneEstatus = Schema::hasColumn('users', 'estatus');

state(['tieneEstatus' => $tieneEstatus]);

$stats = computed(function () {
    try {
        $usuarios = collect(app(UsuariosDataService::class)->listar());
    } catch (\Throwable $e) {
        return ['total' => 0, 'marcados' => 0, 'administradores' => 0, 'nuevos' => 0];
    }

    return [
        'total' => $usuarios->count(),
        // Solo cuenta real si existe users.estatus; si no, 0 y la card no se muestra.
        'marcados' => $this->tieneEstatus
            ? $usuarios->where('estatus', 'marcado')->count()
            : 0,
        'administradores' => $usuarios->where('rol', 'admin')->count(),
        'nuevos' => $usuarios->filter(function ($u) {
            if (empty($u['created_at'])) {
                return false;
            }

            return \Illuminate\Support\Carbon::parse($u['created_at'])->gte(now()->subDays(7));
        })->count(),
    ];
});
?>

<div class="space-y-6" x-on:usuario-actualizado.window="$wire.$refresh()">

    {{-- Estadísticas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $tieneEstatus ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-4">
        <x-admin.stat-mini-card
            label="Total de usuarios"
            :value="number_format($this->stats['total'])"
            trend="Registrados en la plataforma"
            icon-bg="bg-[#D81B60]/10"
            icon-color="text-[#D81B60]"
            border-color="border-[#D81B60]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </x-admin.stat-mini-card>

        @if ($tieneEstatus)
        <x-admin.stat-mini-card
            label="Cuentas marcadas"
            :value="$this->stats['marcados']"
            trend="Requieren revisión"
            trend-color="text-rose-500"
            icon-bg="bg-rose-100"
            icon-color="text-rose-500"
            border-color="border-rose-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h1.5V16.5h9l-.75-3 .75-3h-9V3H3z" />
            </svg>
        </x-admin.stat-mini-card>
        @endif

        <x-admin.stat-mini-card
            label="Administradores"
            :value="$this->stats['administradores']"
            trend="Con acceso al panel"
            trend-color="text-amber-600"
            icon-bg="bg-amber-100"
            icon-color="text-amber-600"
            border-color="border-amber-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
            </svg>
        </x-admin.stat-mini-card>

        <x-admin.stat-mini-card
            label="Nuevos registros"
            :value="$this->stats['nuevos']"
            trend="Últimos 7 días"
            trend-color="text-emerald-600"
            icon-bg="bg-emerald-100"
            icon-color="text-emerald-600"
            border-color="border-emerald-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
        </x-admin.stat-mini-card>
    </div>

    {{-- Tabla --}}
    <livewire:admin.usuario.table />

    <livewire:admin.usuario.form />
</div>
