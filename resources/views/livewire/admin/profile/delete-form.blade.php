<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    /** 0 cerrado · 1 advertencia · 2 confirmación fuerte */
    public int $paso = 0;

    public bool $acepto_riesgo = false;
    public string $confirmacion = '';
    public string $password = '';
    public ?string $errorGeneral = null;

    public function abrirPaso1(): void
    {
        $this->resetErrorBag();
        $this->errorGeneral = null;
        $this->acepto_riesgo = false;
        $this->confirmacion = '';
        $this->password = '';
        $this->paso = 1;
    }

    public function cerrar(): void
    {
        $this->paso = 0;
        $this->resetErrorBag();
        $this->errorGeneral = null;
        $this->acepto_riesgo = false;
        $this->confirmacion = '';
        $this->password = '';
    }

    public function irPaso2(): void
    {
        $this->resetErrorBag();
        $this->errorGeneral = null;

        if (! $this->acepto_riesgo) {
            $this->addError('acepto_riesgo', 'Marca la casilla para confirmar que entiendes el riesgo.');

            return;
        }

        $this->paso = 2;
    }

    public function volverPaso1(): void
    {
        $this->password = '';
        $this->confirmacion = '';
        $this->resetErrorBag();
        $this->errorGeneral = null;
        $this->paso = 1;
    }

    public function deleteUser(Logout $logout): void
    {
        $this->resetErrorBag();
        $this->errorGeneral = null;

        $user = Auth::user();
        if (! $user) {
            $this->errorGeneral = 'No hay una sesión activa. Vuelve a iniciar sesión.';

            return;
        }

        // Solo la cuenta autenticada actual (nunca por id de terceros).
        $this->validate([
            'confirmacion' => ['required', 'string', 'in:ELIMINAR'],
            'password' => ['required', 'string', 'current_password'],
            'acepto_riesgo' => ['accepted'],
        ], [
            'confirmacion.required' => 'Escribe la palabra de confirmación.',
            'confirmacion.in' => 'Debes escribir exactamente: ELIMINAR',
            'password.required' => 'Escribe tu contraseña.',
            'password.current_password' => 'La contraseña no es correcta. No se eliminó nada.',
            'acepto_riesgo.accepted' => 'Debes aceptar la advertencia para continuar.',
        ]);

        // Protección: no borrar el último administrador del sistema.
        if ($user->hasRole('admin')) {
            $otrosAdmins = User::role('admin')->where('id', '!=', $user->id)->count();
            if ($otrosAdmins === 0) {
                $this->errorGeneral = 'No se puede eliminar la única cuenta de administrador del sistema. Crea otro administrador antes de borrar esta.';
                $this->password = '';

                return;
            }
        }

        // Doble verificación de identidad de sesión.
        if ((int) Auth::id() !== (int) $user->id) {
            $this->errorGeneral = 'La sesión no coincide. Por seguridad no se eliminó la cuenta.';

            return;
        }

        // Borrar primero (con el modelo en memoria), luego cerrar sesión.
        $user->delete();
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-5">
    <header>
        <h2 class="text-lg font-bold text-rose-700">Zona delicada</h2>
        <p class="mt-1 text-sm text-neutral-600">
            Eliminar tu cuenta es permanente. Usa esta opción solo si estás seguro.
        </p>
    </header>

    <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-rose-900">Esta acción eliminará tu cuenta</p>
                <p class="text-xs text-rose-800/80 mt-1 leading-relaxed">
                    Se cerrará tu sesión y perderás el acceso al panel. No se puede deshacer con un solo clic: pediremos varias confirmaciones.
                </p>
                <button type="button" wire:click="abrirPaso1"
                    class="mt-4 inline-flex items-center rounded-full border border-rose-300 bg-white px-5 py-2 text-sm font-bold text-rose-700 hover:bg-rose-100 transition">
                    Eliminar mi cuenta
                </button>
            </div>
        </div>
    </div>

    {{-- Paso 1: advertencia --}}
    @if ($paso === 1)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-neutral-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-neutral-100 bg-rose-50">
                <h3 class="text-lg font-bold text-rose-900">Confirma que deseas continuar</h3>
                <p class="text-sm text-rose-800/90 mt-1">Paso 1 de 2 · Lectura obligatoria</p>
            </div>
            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-neutral-700 leading-relaxed">
                    Esta acción es delicada. Si eliminas tu cuenta de administrador:
                </p>
                <ul class="text-sm text-neutral-600 space-y-2 list-disc pl-5">
                    <li>Perderás el acceso al panel de inmediato.</li>
                    <li>No podrás recuperar la cuenta con un simple “deshacer”.</li>
                    <li>Solo continúa si estás completamente seguro.</li>
                </ul>

                <label class="flex items-start gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-3 cursor-pointer">
                    <input type="checkbox" wire:model="acepto_riesgo" class="mt-0.5 rounded border-neutral-300 text-[#D81B60] focus:ring-[#D81B60]" />
                    <span class="text-sm text-neutral-800">Entiendo que esta acción es permanente y no debo hacerla por error.</span>
                </label>
                @error('acepto_riesgo') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="cerrar"
                        class="rounded-full px-4 py-2 text-sm font-semibold text-neutral-600 hover:bg-neutral-100">
                        Cancelar
                    </button>
                    <button type="button" wire:click="irPaso2"
                        class="rounded-full bg-rose-600 px-5 py-2 text-sm font-bold text-white hover:bg-rose-700">
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Paso 2: palabra + contraseña --}}
    @if ($paso === 2)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-neutral-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-neutral-100 bg-rose-50">
                <h3 class="text-lg font-bold text-rose-900">Escribe una confirmación para continuar</h3>
                <p class="text-sm text-rose-800/90 mt-1">Paso 2 de 2 · Validación final</p>
            </div>
            <form wire:submit="deleteUser" class="px-6 py-5 space-y-4">
                @if ($errorGeneral)
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 font-medium">
                    {{ $errorGeneral }}
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">
                        Escribe <span class="text-rose-700">ELIMINAR</span> para confirmar
                    </label>
                    <input wire:model="confirmacion" type="text" autocomplete="off"
                        placeholder="ELIMINAR"
                        class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm font-semibold tracking-wide uppercase focus:border-rose-500 focus:ring-rose-200" />
                    @error('confirmacion') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-neutral-500 mb-1.5">
                        Tu contraseña
                    </label>
                    <input wire:model="password" type="password" autocomplete="current-password"
                        class="w-full rounded-xl border-neutral-200 bg-neutral-50 text-sm focus:border-rose-500 focus:ring-rose-200" />
                    @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-between gap-2 pt-2">
                    <button type="button" wire:click="volverPaso1"
                        class="rounded-full px-4 py-2 text-sm font-semibold text-neutral-600 hover:bg-neutral-100">
                        Atrás
                    </button>
                    <div class="flex gap-2">
                        <button type="button" wire:click="cerrar"
                            class="rounded-full px-4 py-2 text-sm font-semibold text-neutral-600 hover:bg-neutral-100">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="rounded-full bg-rose-700 px-5 py-2 text-sm font-bold text-white hover:bg-rose-800">
                            Eliminar definitivamente
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</section>
