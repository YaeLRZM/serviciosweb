<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.mobile')] class extends Component
{
    /**
     * Log the current user out.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('mobile.login', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen flex flex-col items-center justify-center px-6 text-center" style="font-family: 'Poppins', sans-serif;">
    <span style="font-family: 'Cinzel', serif;" class="text-xs tracking-[0.3em] text-[#D81B60] font-semibold mb-3">
        IXÉ MODA
    </span>
    <h1 class="text-2xl text-gray-900 mb-2">¡Bienvenida, {{ auth()->user()->name }}!</h1>
    <p class="text-sm text-gray-600 mb-8">Esta pantalla es un placeholder temporal. Aquí irá el home de la app móvil.</p>

    <button wire:click="logout" class="text-sm text-[#D81B60] font-medium hover:underline">
        Cerrar sesión
    </button>
</div>
