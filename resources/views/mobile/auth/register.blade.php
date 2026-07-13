<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.mobile')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('mobile.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="relative min-h-screen flex items-center justify-center overflow-hidden px-5 py-10">
    <div class="absolute inset-0 z-0">
        <div class="w-full h-full bg-cover bg-center scale-105" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB-BO97Dohm-vb0z_dn6yjQQmNLMH4MQy6IHIbq3kLFZnv0Sy3crixRIBSYXpuEojvrBLXrppdKe0RdtknSWyzwnGQC9g3bQKf3pUvpOcxKRfAjh71S9i8QFYJFbRu6bxPCWT1kXbzDleBZ-klhV4GO_l41Qk8a1yKbtin-6jt75hlzyziYoFDRaZ33RI8Vh-FEN7go-51BgtOitSeMNYR0GoIrGbjM9RNmIkXYoQgBS1GLHPsj34rjtjlWqESfR5MHd6LfIj-1w7TX'); filter: blur(30px) saturate(1.2) brightness(0.85);"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/40"></div>
    </div>

    <div class="relative z-10 w-full max-w-sm">

        <div class="text-center mb-8">
            <span style="font-family: 'Cinzel', serif;" class="block text-xs tracking-[0.3em] text-white/90 font-semibold mb-3">
                IXÉ MODA
            </span>
            <h1 style="font-family: 'Cormorant Garamond', serif;" class="text-4xl italic text-white leading-tight">
                Crea tu cuenta
            </h1>
        </div>

        <div class="bg-gradient-to-b from-[#F8F5F2]/70 to-[#FFE7CE]/70 backdrop-blur-md border border-white/20 rounded-[2.5rem] p-8 shadow-2xl">

            <form wire:submit="register" class="space-y-5" style="font-family: 'Poppins', sans-serif;">

                <div>
                    <div class="relative flex items-center">
                        <input wire:model="name"
                            id="name"
                            type="text"
                            name="name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Nombre completo"
                            class="w-full bg-white/40 border border-gray-600/40 text-gray-800 text-sm rounded-full focus:ring-2 focus:ring-[#D81B60] focus:border-[#D81B60] block px-5 py-4 pr-12 placeholder-gray-600 outline-none transition" />

                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-700">
                            <x-icon.user class="w-5 h-5" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <div class="relative flex items-center">
                        <input wire:model="email"
                            id="email"
                            type="email"
                            name="email"
                            required
                            autocomplete="username"
                            placeholder="Correo electrónico"
                            class="w-full bg-white/40 border border-gray-600/40 text-gray-800 text-sm rounded-full focus:ring-2 focus:ring-[#D81B60] focus:border-[#D81B60] block px-5 py-4 pr-12 placeholder-gray-600 outline-none transition" />

                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-700">
                            <x-icon.mail class="w-5 h-5" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div x-data="{ show: false }">
                    <div class="relative flex items-center">
                        <input wire:model="password"
                            id="password"
                            :type="show ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Contraseña"
                            class="w-full bg-white/40 border border-gray-600/40 text-gray-800 text-sm rounded-full focus:ring-2 focus:ring-[#D81B60] focus:border-[#D81B60] block px-5 py-4 pr-12 placeholder-gray-600 outline-none transition" />

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
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <div class="relative flex items-center">
                        <input wire:model="password_confirmation"
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirmar contraseña"
                            class="w-full bg-white/40 border border-gray-600/40 text-gray-800 text-sm rounded-full focus:ring-2 focus:ring-[#D81B60] focus:border-[#D81B60] block px-5 py-4 pr-12 placeholder-gray-600 outline-none transition" />

                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-700">
                            <x-icon.lock class="w-5 h-5" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div>
                    <label for="terms" class="flex items-start gap-2 cursor-pointer text-sm text-gray-700 px-1">
                        <input wire:model="terms"
                            id="terms"
                            type="checkbox"
                            class="mt-0.5 rounded border-gray-500 text-[#D81B60] shadow-sm focus:ring-[#D81B60] bg-transparent" />
                        <span>Acepto los términos y condiciones</span>
                    </label>
                    <x-input-error :messages="$errors->get('terms')" class="mt-2" />
                </div>

                <button type="submit"
                    class="w-full bg-[#D81B60] text-white font-medium text-base rounded-full px-5 py-4 shadow-lg shadow-[#D81B60]/30 hover:bg-[#c01553] active:scale-[0.98] focus:outline-none transition-all">
                    Registrarse
                </button>

                <p class="text-center text-sm text-gray-700">
                    ¿Ya tienes una cuenta?
                    <a href="{{ route('mobile.login') }}" wire:navigate class="text-[#D81B60] font-semibold hover:underline">
                        Inicia sesión
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>
