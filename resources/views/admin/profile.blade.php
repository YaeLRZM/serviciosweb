@php
    $user = auth()->user();
    $nombre = $user?->nombre_completo ?? 'Administrador';
    $email = $user?->email ?? '';
    $rol = $user?->rol === 'admin' ? 'Administrador' : ($user?->rol ?? 'Cuenta');
@endphp

<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-cormorant text-2xl text-neutral-900">Mi perfil</h2>
    </x-slot>

    <div class="-m-6 p-6 min-h-full space-y-6">
        {{-- Cabecera de perfil con identidad Ixé --}}
        <div class="relative overflow-hidden rounded-3xl border border-pink-100/80 bg-white shadow-sm">
            <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-r from-[#D81B60] via-[#E91E63] to-[#F48FB1]"></div>
            <div class="relative px-6 sm:px-8 pt-14 pb-6 flex flex-col sm:flex-row sm:items-end gap-5">
                {{-- Ícono admin (no imagen genérica) --}}
                <div class="w-24 h-24 rounded-2xl bg-white border-4 border-white shadow-md flex items-center justify-center shrink-0">
                    <div class="w-full h-full rounded-xl bg-[#F3E5E8] flex items-center justify-center text-[#D81B60]">
                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                </div>

                <div class="flex-1 min-w-0 pb-1">
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-[#D81B60]/10 text-[#D81B60] text-[11px] font-bold uppercase tracking-wide px-2.5 py-1 mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D81B60]"></span>
                        {{ $rol }}
                    </div>
                    <h1 class="font-cormorant text-3xl text-neutral-900 leading-tight truncate">{{ $nombre }}</h1>
                    <p class="text-sm text-neutral-500 mt-0.5 truncate">{{ $email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="sm:pb-1">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-50 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H9" />
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Columna principal --}}
            <div class="xl:col-span-2 space-y-6">
                <div class="rounded-3xl border border-neutral-100 bg-white shadow-sm p-6 sm:p-8">
                    <livewire:admin.profile.info-form />
                </div>

                <div class="rounded-3xl border border-neutral-100 bg-white shadow-sm p-6 sm:p-8">
                    <livewire:admin.profile.password-form />
                </div>
            </div>

            {{-- Lateral --}}
            <div class="space-y-6">
                <div class="rounded-3xl border border-neutral-100 bg-white shadow-sm p-6">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-neutral-400 mb-4">Tu cuenta</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-neutral-500">Rol</dt>
                            <dd class="font-semibold text-neutral-900">{{ $rol }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-neutral-500">Correo</dt>
                            <dd class="font-semibold text-neutral-900 text-right break-all">{{ $email }}</dd>
                        </div>
                        @if ($user?->telefono)
                        <div class="flex justify-between gap-3">
                            <dt class="text-neutral-500">Teléfono</dt>
                            <dd class="font-semibold text-neutral-900">{{ $user->telefono }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between gap-3">
                            <dt class="text-neutral-500">Miembro desde</dt>
                            <dd class="font-semibold text-neutral-900">
                                {{ optional($user?->created_at)->format('d/m/Y') ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-3xl border border-pink-100 bg-[#F8F5F2] p-6">
                    <div class="w-10 h-10 rounded-full bg-[#D81B60]/10 text-[#D81B60] flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-900">Consejo de seguridad</h3>
                    <p class="text-xs text-neutral-600 mt-1.5 leading-relaxed">
                        Usa una contraseña distinta a la de otros sitios y ciérrala sesión si compartes el equipo.
                    </p>
                </div>

                <div class="rounded-3xl border border-neutral-100 bg-white shadow-sm p-6 sm:p-8">
                    <livewire:admin.profile.delete-form />
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
