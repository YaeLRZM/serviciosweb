<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.mobile')] class extends Component
{
    //
}; ?>

<div class="relative h-screen w-full overflow-hidden">
    <div class="fixed inset-0 z-0">
        <div class="w-full h-full bg-cover bg-center scale-105 brightness-95" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB-BO97Dohm-vb0z_dn6yjQQmNLMH4MQy6IHIbq3kLFZnv0Sy3crixRIBSYXpuEojvrBLXrppdKe0RdtknSWyzwnGQC9g3bQKf3pUvpOcxKRfAjh71S9i8QFYJFbRu6bxPCWT1kXbzDleBZ-klhV4GO_l41Qk8a1yKbtin-6jt75hlzyziYoFDRaZ33RI8Vh-FEN7go-51BgtOitSeMNYR0GoIrGbjM9RNmIkXYoQgBS1GLHPsj34rjtjlWqESfR5MHd6LfIj-1w7TX');"></div>
    </div>

    <div class="fixed inset-0 z-10 bg-gradient-to-t from-black/60 via-black/20 to-transparent pointer-events-none"></div>

    <main class="relative z-20 flex flex-col items-center justify-end h-full w-full px-5 pb-12 text-center">

        <div id="header-content" class="mb-10 space-y-3 animate-[fade-in-up_1s_ease-out_0.3s_both]">
            <h1 style="font-family: 'Cinzel Decorative', serif;" class="text-white text-[56px] leading-[64px] tracking-[0.15em] font-bold drop-shadow-lg">
                IXÉ MODA
            </h1>
            <p style="font-family: 'Cormorant Garamond', serif;" class="italic text-white text-2xl opacity-90">
                Historia en cada hilo
            </p>
        </div>

        <div id="cta-buttons" class="w-full max-w-sm flex flex-col gap-4 animate-[fade-in-up_1s_ease-out_0.8s_both]">
            <a href="{{ route('mobile.register') }}"
                wire:navigate
                class="w-full py-4 bg-[#D81B60] text-white rounded-full font-medium text-base shadow-lg shadow-[#D81B60]/30 active:scale-95 transition-all duration-200"
                style="font-family: 'Poppins', sans-serif;">
                Registrarme
            </a>

            <a href="{{ route('mobile.login') }}"
                wire:navigate
                class="w-full py-4 border border-[#D81B60] text-[#D81B60] bg-white/10 backdrop-blur-sm rounded-full font-medium text-base active:scale-95 transition-all duration-200"
                style="font-family: 'Poppins', sans-serif;">
                Iniciar Sesión
            </a>

            <a href="{{ url('/') }}"
                wire:navigate
                class="w-full py-2 text-white/60 text-xs uppercase tracking-widest hover:text-white transition-all duration-200 active:scale-95"
                style="font-family: 'Poppins', sans-serif;">
                Continuar como invitado
            </a>
        </div>

        <div class="mt-6">
            <p style="font-family: 'Poppins', sans-serif;" class="text-xs text-white/60 tracking-widest uppercase">
                Herencia • Elegancia • Diseño
            </p>
        </div>
    </main>

    <style>
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(1rem); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</div>
