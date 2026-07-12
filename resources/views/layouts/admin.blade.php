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

            @if (isset($header))
            <header class="bg-white border-b border-neutral-200/60 shadow-sm">
                <div class="px-6 py-5">
                    {{ $header }}
                </div>
            </header>
            @endif

            <main class="p-6 flex-1">
                {{ $slot }}
            </main>
        </div>

    </div>

    @fluxScripts
    @livewireScripts
</body>

</html>