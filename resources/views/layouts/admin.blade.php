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
                    <div class="flex items-center gap-4 md:gap-8">

                        <!-- 1. Buscador Estilizado (Estilo Píldora como en la imagen) -->
                        <div class="relative hidden sm:block w-72 md:w-96">
                            <input type="text"
                                placeholder="Buscar productos, artesanos o ventas..."
                                class="w-full px-6 py-2.5 bg-white border border-pink-100/80 rounded-full text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60] transition-colors shadow-sm">
                        </div>

                        <!-- 2. Campanita de Notificaciones -->
                        <button class="relative p-2 text-gray-600 hover:text-[#D81B60] transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <!-- Punto estático rosa (como en la imagen) -->
                            <span class="absolute top-2 right-2 flex h-2 w-2">
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#D81B60] border border-white"></span>
                            </span>
                        </button>

                        <!-- 3. Información del Usuario y Foto de Perfil -->
                        <div class="flex items-center gap-3 pl-2">
                            <!-- Textos del perfil -->
                            <div class="hidden md:flex flex-col text-right">
                                <span class="text-sm font-bold text-[#2B2B2B] leading-none mb-1">Admin Oaxaca</span>
                                <span class="text-[11px] font-medium text-gray-500 leading-none">Super Admin</span>
                            </div>

                            <!-- Avatar con borde sutil -->
                            <button class="flex text-sm bg-white rounded-full focus:ring-2 focus:ring-[#D81B60] transition flex-shrink-0 p-0.5 border-2 border-pink-100/60">
                                <img class="h-10 w-10 rounded-full object-cover"
                                    src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80"
                                    alt="Avatar del Administrador">
                            </button>
                        </div>

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