<?php

use App\Services\Api\DashboardApiService;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Component;

new class extends Component {
    public array $reportes_seguridad = [];
    public bool $cargando = true;
    public ?string $error = null;

    public function mount(): void
    {
        $this->cargarAlertas();
    }

    private bool $usarMock = true;

    public function cargarAlertas(): void
    {
        $this->cargando = true;
        $this->error = null;

        if ($this->usarMock) {
            $this->reportes_seguridad = [
                [
                    'tipo' => 'Publicación reportada',
                    'motivo' => 'Varios usuarios reportaron una publicación por posible contenido ofensivo.',
                    'urgente' => true,
                ],
                [
                    'tipo' => 'Producto sospechoso',
                    'motivo' => 'Se detectó un producto con imágenes duplicadas y descripción inconsistente.',
                    'urgente' => false,
                ],
                [
                    'tipo' => 'Usuario reportado',
                    'motivo' => 'Un vendedor recibió múltiples reportes por incumplimiento en los envíos.',
                    'urgente' => true,
                ],
                [
                    'tipo' => 'Comentario inapropiado',
                    'motivo' => 'Se encontró lenguaje ofensivo en una reseña publicada recientemente.',
                    'urgente' => false,
                ],
            ];
        } else {
            $respuesta = app(DashboardApiService::class)->alertasModeracion();

            if ($respuesta->successful()) {
                $this->reportes_seguridad = $respuesta->json('data', []);
            } else {
                $this->error = 'No se pudieron cargar las alertas de moderación.';
                $this->reportes_seguridad = [];
            }
        }

        $this->cargando = false;
    }

    public function verTodasLasAlertas()
    {
        if (! Route::has('admin.alertas.index')) {
            session()->flash('info', 'El módulo de alertas de moderación está en construcción.');
            return;
        }

        return $this->redirect(route('admin.alertas.index'), navigate: true);
    }
};
?>

<div class="bg-white rounded-[2rem] shadow-sm border border-gray-50 p-6 flex flex-col h-[410px]">
    <div class="flex justify-between items-center mb-4 flex-shrink-0">
        <h3 class="text-lg font-bold text-[#D81B60] flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Alertas de Moderación
        </h3>
        <span class="bg-red-50 text-red-500 text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider">
            Urgente
        </span>
    </div>

    @if (session('info'))
    <div class="bg-blue-50 text-blue-600 text-xs rounded-xl p-3 mb-3 flex-shrink-0">{{ session('info') }}</div>
    @endif

    <div class="space-y-4 flex-1 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-200">
        @if ($error)
        <div class="bg-red-50 text-red-600 text-sm rounded-xl p-4">{{ $error }}</div>
        @elseif ($cargando)
        @for ($i = 0; $i < 3; $i++)
            <div class="h-24 bg-gray-100 rounded-2xl animate-pulse">
    </div>
    @endfor
    @elseif (empty($reportes_seguridad))
    <div class="h-full flex items-center justify-center text-center">
        <p class="text-sm text-gray-400">No hay alertas de moderación pendientes.</p>
    </div>
    @else
    @foreach($reportes_seguridad as $alerta)
    <div class="border border-gray-100 rounded-2xl p-4 flex gap-3 relative overflow-hidden bg-[#FAFAFA]/50">
        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ ($alerta['urgente'] ?? false) ? 'bg-red-500' : 'bg-yellow-400' }}"></div>

        <div class="flex-1 pl-2">
            <h4 class="text-sm font-bold text-gray-800 mb-1">{{ $alerta['tipo'] }}</h4>
            <p class="text-xs text-gray-500 leading-relaxed mb-3">
                {{ Str::limit($alerta['motivo'], 65) }}
            </p>
            <div class="flex gap-3">
                <button class="bg-[#D81B60] text-white text-[10px] font-bold px-4 py-1.5 rounded-full hover:bg-[#ad144b] transition">
                    {{ ($alerta['urgente'] ?? false) ? 'Revisar' : 'Investigar' }}
                </button>
                @if($alerta['urgente'] ?? false)
                <button class="text-gray-400 text-[10px] font-bold px-2 py-1.5 hover:text-gray-600 transition">
                    Ignorar
                </button>
                @endif
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>

<div class="mt-4 text-center flex-shrink-0">
    <button
        wire:click="verTodasLasAlertas"
        class="group inline-flex items-center justify-center gap-2 w-full py-2.5 text-sm font-bold text-[#D81B60] bg-[#D81B60]/10 rounded-full hover:bg-[#D81B60] hover:text-white transition-all duration-300">
        Ver todas las alertas
        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
    </button>
</div>
</div>