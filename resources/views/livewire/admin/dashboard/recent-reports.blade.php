<?php

use App\Services\Dashboard\DashboardStatsService;
use Livewire\Volt\Component;

new class extends Component {
    public array $reportes_seguridad = [];
    public bool $cargando = true;
    public ?string $error = null;
    public bool $mostrarModal = false;

    public function mount(): void
    {
        $this->cargarAlertas();
    }

    public function cargarAlertas(): void
    {
        $this->cargando = true;
        $this->error = null;

        try {
            $this->reportes_seguridad = app(DashboardStatsService::class)->alertasOperativas();
        } catch (\Throwable $e) {
            $this->error = 'No se pudieron cargar las alertas. Intenta de nuevo.';
            $this->reportes_seguridad = [];
        }

        $this->cargando = false;
    }

    public function toggleModal(): void
    {
        $this->mostrarModal = ! $this->mostrarModal;
    }

    /**
     * Navega a la vista filtrada asociada a la alerta (datos reales).
     */
    public function revisarAlerta(string $id): mixed
    {
        $alerta = collect($this->reportes_seguridad)->firstWhere('id', $id);
        $url = is_array($alerta) ? ($alerta['url'] ?? null) : null;

        if (is_string($url) && $url !== '') {
            return $this->redirect($url, navigate: true);
        }

        // Fallback seguro si la alerta ya no existe.
        return $this->redirect(route('admin.ventas.index'), navigate: true);
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
                Alertas operativas
            </h3>
            @if (! empty($reportes_seguridad))
            <span class="bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider">
                {{ count($reportes_seguridad) }} por revisar
            </span>
            @endif
        </div>

        <div class="space-y-3 flex-1 overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-gray-200">
            @if ($error)
            <div class="bg-red-50 text-red-600 text-sm rounded-xl p-4">{{ $error }}</div>
            @elseif ($cargando)
            @for ($i = 0; $i < 3; $i++)
                <div class="h-24 bg-gray-100 rounded-2xl animate-pulse"></div>
            @endfor
            @elseif (empty($reportes_seguridad))
            <div class="h-full min-h-[200px] flex flex-col items-center justify-center text-center px-4 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/40">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-neutral-800">No hay alertas importantes por revisar en este momento</p>
                <p class="text-xs text-neutral-500 mt-1 max-w-xs leading-relaxed">
                    Cuando aparezcan compras canceladas, reseñas bajas u otros casos relevantes, se mostrarán aquí.
                </p>
            </div>
            @else
            @foreach($reportes_seguridad as $alerta)
            <div class="border border-gray-100 rounded-2xl p-4 flex gap-3 relative overflow-hidden bg-[#FAFAFA]/50">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ ($alerta['urgente'] ?? false) ? 'bg-red-500' : 'bg-amber-400' }}"></div>

                <div class="flex-1 pl-2 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h4 class="text-sm font-bold text-gray-800 leading-snug">{{ $alerta['tipo'] }}</h4>
                        @if (! empty($alerta['etiqueta']))
                        <span class="shrink-0 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                            {{ ($alerta['urgente'] ?? false) ? 'bg-red-50 text-red-600' : 'bg-neutral-100 text-neutral-600' }}">
                            {{ $alerta['etiqueta'] }}
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed mb-3">
                        {{ \Illuminate\Support\Str::limit($alerta['motivo'], 110) }}
                    </p>
                    <button
                        type="button"
                        wire:click="revisarAlerta('{{ $alerta['id'] }}')"
                        class="bg-[#D81B60] text-white text-[10px] font-bold px-4 py-1.5 rounded-full hover:bg-[#ad144b] transition">
                        Revisar
                    </button>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        @if (! empty($reportes_seguridad))
        <div class="mt-4 text-center flex-shrink-0">
            <button
                type="button"
                wire:click="toggleModal"
                class="group inline-flex items-center justify-center gap-2 w-full py-2.5 text-sm font-bold text-[#D81B60] bg-[#D81B60]/10 rounded-full hover:bg-[#D81B60] hover:text-white transition-all duration-300">
                Ver todas las alertas
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </button>
        </div>
        @endif
    </div>

    @if ($mostrarModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center p-6 border-b border-gray-100 bg-gray-50/50">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Alertas operativas</h2>
                    <p class="text-xs text-neutral-500 mt-0.5">Casos reales que conviene revisar en el panel</p>
                </div>
                <button type="button" wire:click="toggleModal" class="text-gray-400 hover:text-[#D81B60] transition-colors rounded-full p-1 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                @forelse ($reportes_seguridad as $alerta)
                <div class="border border-gray-100 rounded-2xl p-5 relative overflow-hidden bg-[#FAFAFA]/70 hover:bg-white hover:shadow-md transition-all">
                    <div class="absolute left-0 top-0 bottom-0 w-2 {{ ($alerta['urgente'] ?? false) ? 'bg-red-500' : 'bg-amber-400' }}"></div>
                    <div class="pl-3">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <h4 class="text-base font-bold text-gray-800">{{ $alerta['tipo'] }}</h4>
                            <div class="flex items-center gap-2 shrink-0">
                                @if ($alerta['urgente'] ?? false)
                                <span class="text-[10px] font-bold px-2 py-1 rounded-md uppercase bg-red-50 text-red-600">Urgente</span>
                                @endif
                                @if (! empty($alerta['etiqueta']))
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider bg-neutral-100 text-neutral-600">
                                    {{ $alerta['etiqueta'] }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $alerta['motivo'] }}</p>
                        <div class="flex justify-end">
                            <button
                                type="button"
                                wire:click="revisarAlerta('{{ $alerta['id'] }}')"
                                class="bg-[#D81B60] text-white text-xs font-bold px-5 py-2 rounded-full hover:bg-[#ad144b] transition flex items-center gap-2">
                                Revisar
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-10">
                    <p class="text-sm font-semibold text-neutral-700">No hay alertas importantes por revisar en este momento</p>
                    <p class="text-xs text-neutral-500 mt-1">Todo en orden con los indicadores actuales.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
