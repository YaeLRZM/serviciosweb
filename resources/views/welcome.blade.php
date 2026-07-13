<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ixé Moda</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Animación de flotar (subir y bajar) */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        /* Declaración de la fuente Cinzel por seguridad */
        .font-cinzel {
            font-family: 'Cinzel', serif;
        }
    </style>
</head>

<body class="antialiased h-full overflow-hidden text-white selection:bg-[#D81B60] selection:text-white">

    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-cover bg-center scale-105"
            style="background-image: url('{{ asset('images/fondoWel.png') }}');">
        </div>

        <div class="absolute inset-0 bg-black/40 backdrop-blur-xl"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-[#D81B60]/10 via-black/30 to-black/80"></div>
    </div>

    <main class="relative z-10 flex flex-col items-center justify-between h-full px-6 py-12 md:py-16">

        <div></div>

        <div class="animate-float flex flex-col items-center w-full max-w-sm text-center">

            <h1 class="font-cinzel text-5xl md:text-6xl text-white tracking-widest leading-tight mb-4 drop-shadow-lg">
                IXÉ<br>MODA
            </h1>

            <p class="text-xl md:text-2xl text-white/90 italic font-light mb-12 drop-shadow-md">
                Historia en cada hilo
            </p>

            <div class="w-full flex flex-col gap-5 mt-4">
                <a href="#"
                    class="w-full py-4 rounded-full bg-[#D81B60] text-white font-semibold tracking-wider hover:bg-[#b51751] transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg shadow-[#D81B60]/40 text-center">
                    Empezar
                </a>

                <a href="{{ route('login') }}"
                    class="w-full py-4 rounded-full border border-white/20 bg-white/5 text-[#D81B60] font-semibold tracking-wider hover:bg-white/10 hover:border-white/40 transition-all duration-300 hover:scale-105 active:scale-95 backdrop-blur-md text-center shadow-md">
                    Iniciar Sesión
                </a>
            </div>
        </div>

        <div class="mb-4 text-center">
            <p class="text-white/60 text-xs md:text-sm font-semibold tracking-[0.25em] uppercase drop-shadow-md">
                Herencia &bull; Elegancia &bull; Diseño
            </p>
        </div>

    </main>

</body>

</html>