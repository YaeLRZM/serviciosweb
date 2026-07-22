<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ixé Moda') }} · Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="antialiased font-poppins min-h-screen bg-[#F8F5F2]">

    <div class="flex min-h-screen">

        <livewire:layout.sidebar />

        <div class="flex-1 flex flex-col min-w-0">

            <!-- Header General Modificado -->
            <header class="bg-white border-b border-neutral-200/60 shadow-sm sticky top-0 z-40">
                <div class="px-8 h-20 flex items-center justify-between">

                    <!-- LADO IZQUIERDO: Nombre de la ventana actual -->
                    <div class="text-xl font-bold text-[#2B2B2B]">
                        @if (isset($header))
                        {{ $header }}
                        @else
                        Panel de Control
                        @endif
                    </div>

                    <!-- LADO DERECHO: Buscador, Notificaciones y Perfil -->
                    <div class="flex items-center gap-3 md:gap-5">

                        {{-- Búsqueda global real (todas las vistas del admin) --}}
                        <livewire:admin.header.global-search />

                        {{-- Campana de novedades reales --}}
                        <livewire:admin.header.notification-bell />

                        <div class="flex items-center gap-3 pl-1">
                            <!-- Textos del perfil -->
                            <div class="hidden md:flex flex-col text-right">
                                <span class="text-sm font-bold text-[#2B2B2B] leading-none mb-1">
                                    {{ auth()->user()->nombre_completo ?? (auth()->user()->nombre ?? 'Administrador') }}
                                </span>
                                <span class="text-[11px] font-medium text-gray-500 leading-none">Administrador</span>
                            </div>

                            <flux:dropdown position="bottom" align="end">

                                <button
                                    class="flex text-sm bg-white rounded-full focus:ring-2 focus:ring-[#D81B60] transition flex-shrink-0 p-0.5 border-2 border-pink-100/60">

                                    <img
                                        class="h-10 w-10 rounded-full object-cover"
                                        src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80"
                                        alt="Avatar del Administrador">

                                </button>

                                <flux:menu class="bg-[#F8F5F2] text-zinc-900 min-w-64">

                                    <div class="px-4 py-3">
                                        <p class="font-semibold text-zinc-900">
                                            {{ auth()->user()->name ?? 'Administrador' }}
                                        </p>

                                        <p class="text-sm text-zinc-500">
                                            {{ auth()->user()->email }}
                                        </p>
                                    </div>

                                    <flux:menu.separator />

                                    <flux:menu.item
                                        :href="route('admin.profile')"
                                        icon="user"
                                        wire:navigate>

                                        Mi Perfil

                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <button
                                            type="submit"
                                            class="flex w-full items-center gap-2 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 hover:text-red-950 rounded-md transition-all duration-200 text-left cursor-pointer">

                                            <x-icon.arrow-right-start-on-rectangle variant="mini" />

                                            <span>Cerrar sesión</span>

                                        </button>

                                </flux:menu>

                            </flux:dropdown>

                        </div>
                    </div>
            </header>

            <!-- Contenido Principal -->
            <main class="p-8 flex-1">
                {{ $slot }}
            </main>
        </div>

    </div>

    @fluxScripts
    @livewireScripts
</body>

</html>