<?php

use App\Services\Usuarios\UsuariosDataService;
use Illuminate\Support\Facades\Schema;
use function Livewire\Volt\{state, on, rules};

// En modo mock siempre hay estatus; si no, depende de si la columna existe en BD.
$tieneEstatus = config('features.mock_usuarios', true) || Schema::hasColumn('users', 'estatus');

state([
    'isOpen' => false,
    'modo' => 'editar', // 'editar' | 'crear'
    'usuarioId' => null,
    'nombre' => '',
    'email' => '',
    'password' => '',
    'rol' => 'user',
    'estatus' => 'activo',
    'tieneEstatus' => $tieneEstatus,
]);

on(['abrirUsuario' => function ($id) {
    $usuario = app(UsuariosDataService::class)->find($id);

    if (! $usuario) {
        session()->flash('error', 'No se pudo cargar el usuario.');
        return;
    }

    $this->modo = 'editar';
    $this->usuarioId = $id;
    // Preferir nombre de pila real; fallback al nombre completo mapeado
    $this->nombre = $usuario['nombre_raw'] ?? $usuario['nombre'] ?? $usuario['name'] ?? '';
    $this->email = $usuario['email'] ?? '';
    $this->password = '';
    $this->rol = $usuario['rol'] ?? 'user';
    $this->estatus = $usuario['estatus'] ?? 'activo';
    $this->isOpen = true;
}]);

on(['crearUsuario' => function () {
    $this->modo = 'crear';
    $this->usuarioId = null;
    $this->nombre = '';
    $this->email = '';
    $this->password = '';
    $this->rol = 'user';
    $this->estatus = 'activo';
    $this->isOpen = true;
}]);

rules(fn () => [
    'nombre' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'max:255', $this->modo === 'crear'
        ? \Illuminate\Validation\Rule::unique('users', 'email')
        : \Illuminate\Validation\Rule::unique('users', 'email')->ignore($this->usuarioId),
    ],
    'password' => $this->modo === 'crear' ? ['required', 'string', 'min:8'] : ['nullable', 'string', 'min:8'],
]);

$guardar = function () {
    $this->validate();

    try {
        $payload = [
            'nombre' => $this->nombre,
            'email' => $this->email,
            'password' => $this->password,
            'rol' => $this->rol,
        ];
        // Solo enviar estatus si la columna existe en la BD runtime
        if ($this->tieneEstatus) {
            $payload['estatus'] = $this->estatus;
        }

        if ($this->modo === 'crear') {
            app(UsuariosDataService::class)->crear($payload);
            session()->flash('mensaje', 'Usuario creado.');
        } else {
            app(UsuariosDataService::class)->actualizar($this->usuarioId, $payload);
            session()->flash('mensaje', 'Usuario actualizado.');
        }

        $this->isOpen = false;
        $this->dispatch('usuario-actualizado');
    } catch (\Throwable $e) {
        session()->flash('error', 'No se pudo guardar el usuario. Revisa los datos e intenta de nuevo.');
    }
};
?>

<div>
    <x-modal :show="$isOpen" :title="$modo === 'crear' ? 'Nuevo usuario' : 'Editar usuario'" :subtitle="$modo === 'crear' ? null : $email">
        <x-slot name="closeButton">
            <button wire:click="$set('isOpen', false)" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </x-slot>

        <div class="space-y-5">
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Nombre</label>
                <input type="text" wire:model="nombre" class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                @error('nombre') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Correo electrónico</label>
                <input type="email" wire:model="email" class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">
                    Contraseña @if($modo === 'editar') <span class="normal-case font-normal text-gray-400">(dejar en blanco para no cambiarla)</span> @endif
                </label>
                <input type="password" wire:model="password" class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Rol</label>
                <select wire:model="rol" class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm p-3 focus:outline-none focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]">
                    <option value="admin">Administrador</option>
                    <option value="user">Usuario</option>
                    <option value="guest">Invitado</option>
                </select>
            </div>

            @if ($tieneEstatus)
            <div>
                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Estatus</label>
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" wire:click="$set('estatus', 'activo')"
                        class="p-2.5 rounded-xl border-2 text-xs font-semibold transition-all {{ $estatus === 'activo' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-100 text-gray-500 hover:bg-gray-50' }}">
                        Activo
                    </button>
                    <button type="button" wire:click="$set('estatus', 'suspendido')"
                        class="p-2.5 rounded-xl border-2 text-xs font-semibold transition-all {{ $estatus === 'suspendido' ? 'border-neutral-400 bg-neutral-100 text-neutral-700' : 'border-gray-100 text-gray-500 hover:bg-gray-50' }}">
                        Deshabilitado
                    </button>
                    <button type="button" wire:click="$set('estatus', 'marcado')"
                        class="p-2.5 rounded-xl border-2 text-xs font-semibold transition-all {{ $estatus === 'marcado' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-gray-100 text-gray-500 hover:bg-gray-50' }}">
                        Marcado
                    </button>
                </div>
            </div>
            @endif

            <div class="flex justify-end gap-2.5 pt-2">
                <button type="button" wire:click="$set('isOpen', false)" class="text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
                    Cerrar
                </button>

                <button wire:click="guardar" class="text-xs font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-5 py-2.5 rounded-xl shadow-md transition">
                    Guardar
                </button>
            </div>
        </div>
    </x-modal>
</div>
