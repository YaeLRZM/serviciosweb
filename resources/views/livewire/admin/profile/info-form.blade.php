<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $nombre = '';
    public string $apellido_paterno = '';
    public string $apellido_materno = '';
    public string $email = '';
    public string $telefono = '';
    public string $direccion = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->nombre = (string) ($user->nombre ?? '');
        $this->apellido_paterno = (string) ($user->apellido_paterno ?? '');
        $this->apellido_materno = (string) ($user->apellido_materno ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->telefono = (string) ($user->telefono ?? '');
        $this->direccion = (string) ($user->direccion ?? '');
    }

    public function guardar(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $validated = $this->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido_paterno' => ['nullable', 'string', 'max:120'],
            'apellido_materno' => ['nullable', 'string', 'max:120'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'telefono' => ['nullable', 'string', 'max:40'],
            'direccion' => ['nullable', 'string', 'max:500'],
        ], [
            'nombre.required' => 'Escribe tu nombre.',
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'El correo no es válido.',
            'email.unique' => 'Ese correo ya está en uso.',
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated');
        $this->dispatch('perfil-admin-actualizado');
        session()->flash('perfil_ok', 'Tus datos se guardaron correctamente.');
    }
}; ?>

<section class="space-y-5">
    <header>
        <h2 class="text-lg font-bold text-neutral-900">Datos personales</h2>
        <p class="mt-1 text-sm text-neutral-500">Actualiza la información de tu cuenta de administrador.</p>
    </header>

    @if (session('perfil_ok'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
        {{ session('perfil_ok') }}
    </div>
    @endif

    <form wire:submit="guardar" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="nombre" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Nombre</label>
                <input wire:model="nombre" id="nombre" type="text" required
                    class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
                @error('nombre') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="apellido_paterno" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Apellido paterno</label>
                <input wire:model="apellido_paterno" id="apellido_paterno" type="text"
                    class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
                @error('apellido_paterno') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="apellido_materno" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Apellido materno</label>
                <input wire:model="apellido_materno" id="apellido_materno" type="text"
                    class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
                @error('apellido_materno') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Correo</label>
                <input wire:model="email" id="email" type="email" required
                    class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="telefono" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Teléfono</label>
                <input wire:model="telefono" id="telefono" type="text"
                    class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
                @error('telefono') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="direccion" class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">Dirección</label>
                <input wire:model="direccion" id="direccion" type="text"
                    class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm text-neutral-900 focus:border-[#D81B60] focus:ring-[#D81B60]/20" />
                @error('direccion') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#D81B60] px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#b0124a] transition">
                Guardar cambios
            </button>
            <span wire:loading wire:target="guardar" class="text-xs text-[#D81B60] font-semibold">Guardando…</span>
        </div>
    </form>
</section>
