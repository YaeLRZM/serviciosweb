<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Tymon\JWTAuth\Facades\JWTAuth;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $token = JWTAuth::fromUser(auth()->user());
        session(['api_token' => $token]);

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&family=Cinzel+Decorative:wght@400;700;900&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Inter:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100&display=swap');
    </style>

    <div class="absolute inset-0 z-0 bg-cover bg-center blur-[7px] scale-105" style="background-image: url('{{ asset('images/fondoRosa.png') }}');"></div>

    <div class="relative z-10 w-full max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-12">

        <div class="hidden md:flex flex-col justify-center items-start text-[#1A1A1A] w-1/2 select-none">
            <h1 style="font-family: 'Cinzel', serif;" class="text-7xl md:text-8xl font-medium leading-none tracking-wide">
                IXÉ<br>MODA
            </h1>
            <div class="w-full max-w-md h-[1px] bg-[#1A1A1A] my-4"></div>
            <p style="font-family: 'Cinzel Decorative', cursive;" class="text-xl md:text-2xl tracking-widest">
                HISTORIA EN CADA HILO.
            </p>
        </div>

        <div class="w-full max-w-md bg-gradient-to-b from-[#F8F5F2]/60 to-[#FFE7CE]/60 backdrop-blur-sm rounded-[2.5rem] p-10 shadow-xl border border-white/20">

            <h2 style="font-family: 'Cormorant Garamond', serif;" class="text-center text-3xl text-gray-950 mb-8">
                INICIAR SESIÓN
            </h2>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-5">

                <div>
                    <div class="relative flex items-center">
                        <input wire:model="form.email"
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Correo electronico"
                            style="font-family: 'Poppins', sans-serif;"
                            class="w-full bg-[#F8F5F2]/30 border border-gray-600 text-gray-800 text-sm rounded-full focus:ring-[#D81B60] focus:border-[#D81B60] block px-5 py-3 pr-12 placeholder-gray-700 outline-none transition" />

                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-700">
                            <x-icon.mail class="w-5 h-5" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <div x-data="{ show: false }">
                    <div class="relative flex items-center">
                        <input wire:model="form.password"
                            id="password"
                            :type="show ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Contraseña"
                            style="font-family: 'Poppins', sans-serif;"
                            class="w-full bg-[#F8F5F2]/30 border border-gray-600 text-gray-800 text-sm rounded-full focus:ring-[#D81B60] focus:border-[#D81B60] block px-5 py-3 pr-12 placeholder-gray-700 outline-none transition" />

                        <button type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-700 hover:text-gray-900 focus:outline-none">
                            <span x-show="!show">
                                <x-icon.lock class="w-5 h-5" />
                            </span>
                            <span x-show="show" x-cloak>
                                <x-icon.lock-open class="w-5 h-5" />
                            </span>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between pt-1" style="font-family: 'DM Sans', sans-serif;">
                    <label for="remember" class="inline-flex items-center cursor-pointer">
                        <input wire:model="form.remember"
                            id="remember"
                            type="checkbox"
                            class="rounded border-gray-500 text-[#D81B60] shadow-sm focus:ring-[#D81B60] bg-transparent"
                            name="remember">
                        <span class="ms-2 text-sm text-gray-700">{{ __('Recuérdame') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                    <a class="text-sm text-gray-700 hover:text-[#D81B60] transition"
                        href="{{ route('password.request') }}"
                        wire:navigate>
                        {{ __('Olvide la contraseña') }}
                    </a>
                    @endif
                </div>

                <div class="pt-2">
                    <button type="submit"
                        style="font-family: 'Poppins', sans-serif;"
                        class="w-full bg-[#D81B60] text-white font-medium text-lg rounded-[2rem] px-5 py-3 hover:bg-[#c01553] focus:outline-none transition-all shadow-[1px_4px_4px_0px_rgba(230,18,95,0.25)]">
                        Ingresar
                    </button>
                </div>

                <div class="text-center mt-4 text-sm text-gray-700" style="font-family: 'DM Sans', sans-serif;">
                    No tienes una cuenta?
                    <a href="{{ route('register') }}" wire:navigate class="text-[#D81B60] hover:underline font-medium">
                        Registrar
                    </a>
                </div>

                <div class="flex justify-center mt-6">
                    <button type="button" class="flex items-center gap-3 text-gray-800 hover:text-black transition">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                        </svg>
                        <span style="font-family: 'Inter', sans-serif;" class="font-medium text-[15px]">Google</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>