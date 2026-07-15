<?php

use App\Services\Api\DashboardApiService;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Component;

new class extends Component {
    public array $reportes_seguridad = [];
    public bool $cargando = true;
    public ?string $error = null;
    public bool $mostrarModal = false; // Estado para la ventana flotante

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
            // Añadimos la clave 'entidad' para distinguir publicaciones de vendedores
            $this->reportes_seguridad = [
                [
                    'tipo' => 'Publicación reportada',
                    'entidad' => 'publicacion',
                    'motivo' => 'Varios usuarios reportaron una publicación por posible contenido ofensivo.',
                    'urgente' => true,
                ],
                [
                    'tipo' => 'Producto sospechoso',
                    'entidad' => 'publicacion',
                    'motivo' => 'Se detectó un producto con imágenes duplicadas y descripción inconsistente.',
                    'urgente' => false,
                ],
                [
                    'tipo' => 'Usuario reportado',
                    'entidad' => 'vendedor',
                    'motivo' => 'Un vendedor recibió múltiples reportes por incumplimiento en los envíos.',
                    'urgente' => true,
                ],
                [
                    'tipo' => 'Comentario inapropiado',
                    'entidad' => 'vendedor',
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

    // Alternar la visibilidad del modal
    public function toggleModal()
    {
        $this->mostrarModal = !$this->mostrarModal;
    }

    // Lógica para redirigir según el tipo de entidad
    public function revisarReporte($entidad)
    {
        if ($entidad === 'publicacion') {
            return $this->redirect(route('admin.publicacion.index'), navigate: true);
        } elseif ($entidad === 'vendedor') {
            return $this->redirect(route('admin.vendedores.index'), navigate: true);
        }
    }
};
?>

<div>
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
                    <button wire:click="revisarReporte('{{ $alerta['entidad'] ?? 'publicacion' }}')" class="bg-[#D81B60] text-white text-[10px] font-bold px-4 py-1.5 rounded-full hover:bg-[#ad144b] transition">
                        {{ ($alerta['urgente'] ?? false) ? 'Revisar' : 'Investigar' }}
                    </button>
                </div>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    <div class="mt-4 text-center flex-shrink-0">
        <button
            wire:click="toggleModal"
            class="group inline-flex items-center justify-center gap-2 w-full py-2.5 text-sm font-bold text-[#D81B60] bg-[#D81B60]/10 rounded-full hover:bg-[#D81B60] hover:text-white transition-all duration-300">
            Ver todas las alertas
            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </button>
    </div>
</div>

@if($mostrarModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 transition-opacity">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">

        <div class="flex justify-between items-center p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-xl font-bold text-gray-800">Todos los Reportes</h2>
            <button wire:click="toggleModal" class="text-gray-400 hover:text-[#D81B60] transition-colors rounded-full p-1 hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-4 scrollbar-thin scrollbar-thumb-gray-200">
            @foreach($reportes_seguridad as $alerta)
            @php
            $esPublicacion = ($alerta['entidad'] ?? '') === 'publicacion';
            // Azul para publicación, Morado para vendedor
            $colorBorde = $esPublicacion ? 'bg-blue-500' : 'bg-purple-500';
            $colorEtiqueta = $esPublicacion ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600';
            @endphp

            <div class="border border-gray-100 rounded-2xl p-5 flex gap-4 relative overflow-hidden bg-[#FAFAFA]/70 hover:bg-white hover:shadow-md transition-all">
                <div class="absolute left-0 top-0 bottom-0 w-2 {{ $colorBorde }}"></div>

                <div class="flex-1 pl-2">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-base font-bold text-gray-800">{{ $alerta['tipo'] }}</h4>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider {{ $colorEtiqueta }}">
                            {{ $esPublicacion ? 'Publicación' : 'Vendedor' }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-500 leading-relaxed mb-4">
                        {{ $alerta['motivo'] }}
                    </p>

                    <div class="flex justify-end">
                        <button
                            wire:click="revisarReporte('{{ $alerta['entidad'] ?? 'publicacion' }}')"
                            class="bg-[#D81B60] text-white text-xs font-bold px-5 py-2 rounded-full hover:bg-[#ad144b] transition flex items-center gap-2">
                            Revisar
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
</div>