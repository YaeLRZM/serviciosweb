<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ], [
                'current_password.required' => 'Escribe tu contraseña actual.',
                'current_password.current_password' => 'La contraseña actual no es correcta.',
                'password.required' => 'Escribe una contraseña nueva.',
                'password.confirmed' => 'La confirmación no coincide.',
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        session()->flash('password_ok', 'Tu contraseña se actualizó correctamente.');
        $this->dispatch('password-updated');
    }
}; ?>

<section class="space-y-5">
    <header>
        <h2 class="text-lg font-bold text-neutral-900">Contraseña</h2>
        <p class="mt-1 text-sm text-neutral-500">Elige una contraseña segura y que solo tú conozcas.</p>
    </header>

    @if (session('password_ok'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
        {{ session('password_ok') }}
    </div>
    @endif

    <form wire:submit="updatePassword" class="space-y-4">
        <div>
            <label for="current_password" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Contraseña actual</label>
            <input wire:model="current_password" id="current_password" type="password" autocomplete="current-password"
                class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
            @error('current_password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Contraseña nueva</label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password"
                class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
            @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Confirmar contraseña nueva</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password"
                class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#D81B60] px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#b0124a] transition">
                Actualizar contraseña
            </button>
            <span wire:loading wire:target="updatePassword" class="text-xs text-[#D81B60] font-semibold">Guardando…</span>
        </div>
    </form>
</section>
