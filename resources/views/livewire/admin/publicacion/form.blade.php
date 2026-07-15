<?php

use App\Services\Publicaciones\PublicacionesDataService;
use function Livewire\Volt\{state, on};

state([
    'isOpen' => false,
    'pubId' => null,
    'publicacion' => null,
    'error' => null,
]);

on(['abrirDetallePublicacion' => function ($id) {
    $this->pubId = (int) $id;
    $this->error = null;

    try {
        $this->publicacion = app(PublicacionesDataService::class)->find($this->pubId);

        if (! $this->publicacion) {
            $this->error = 'No se encontró la publicación solicitada.';
        }
    } catch (\Throwable $e) {
        $this->error = 'No se pudo cargar el detalle de la publicación.';
        $this->publicacion = null;
    }

    $this->isOpen = true;
}]);

$aplicarDictamen = function (string $estado) {
    if (! $this->pubId) {
        return;
    }

    try {
        app(PublicacionesDataService::class)->actualizarEstado($this->pubId, $estado);
    } catch (\Throwable $e) {
        $this->error = 'No se pudo aplicar el dictamen. Intenta de nuevo.';
        return;
    }

    $this->isOpen = false;
    $this->publicacion = null;
    $this->dispatch('publicacion-actualizada');

    $mensajes = [
        'REVISADO'   => 'La publicación se conservó en el catálogo.',
        'SUSPENDIDO' => 'La publicación fue suspendida temporalmente.',
        'ELIMINADO'  => 'La publicación fue eliminada del catálogo.',
    ];

    session()->flash('mensaje', $mensajes[$estado] ?? 'Dictamen aplicado.');
};
?>

<div>
    @if($isOpen)
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-[2rem] shadow-xl border border-gray-100 max-w-sm w-full overflow-hidden transform transition-all">

            @if ($error)
            <div class="p-6">
                <div class="bg-red-50 text-red-700 text-sm rounded-xl p-4 font-medium">{{ $error }}</div>
                <button wire:click="$set('isOpen', false)" class="w-full mt-4 text-xs font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
                    Cerrar
                </button>
            </div>
            @elseif ($publicacion)
            <div class="bg-[#D81B60] px-6 pt-6 pb-8 text-center relative">
                <button wire:click="$set('isOpen', false)" class="absolute top-4 right-4 text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <h3 class="text-white font-extrabold text-lg uppercase tracking-wide">{{ $publicacion['producto'] }}</h3>
                <p class="text-white/80 text-xs mt-2 px-4">{{ $publicacion['descripcion'] }}</p>
            </div>

            <div class="px-6 -mt-6">
                <img src="{{ $publicacion['imagen'] }}" alt="{{ $publicacion['producto'] }}" class="w-full h-48 object-cover rounded-2xl shadow-md border border-white">
            </div>

            <div class="p-6 space-y-4">
                <div class="flex items-center justify-center gap-1">
                    @php $promedio = round($publicacion['calificacion_promedio'] ?? 0, 1); @endphp
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($promedio) ? 'text-amber-400' : 'text-neutral-200' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.921-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.197-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.813 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.285-3.957z" />
                        </svg>
                        @endfor
                        <span class="text-xs font-bold text-neutral-500 ml-2">{{ $promedio > 0 ? number_format($promedio, 1) : 'Sin reseñas' }}</span>
                </div>

                <div class="text-xs text-neutral-400 flex items-center gap-2 flex-wrap">
                    <span class="bg-neutral-100 px-2.5 py-1 rounded-full font-medium text-neutral-600">{{ $publicacion['categoria'] }}</span>
                    <span>Artesano: {{ $publicacion['artesano'] }}</span>
                    <span>Tienda: {{ $publicacion['tienda'] }}</span>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Dictamen</label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <button wire:click="aplicarDictamen('REVISADO')"
                            class="flex flex-col items-center gap-1.5 p-3 rounded-2xl border-2 border-emerald-200 bg-emerald-50 text-emerald-700 font-bold hover:bg-emerald-100 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span class="text-[11px]">Conservar</span>
                        </button>

                        <button wire:click="aplicarDictamen('SUSPENDIDO')"
                            class="flex flex-col items-center gap-1.5 p-3 rounded-2xl border-2 border-orange-200 bg-orange-50 text-orange-700 font-bold hover:bg-orange-100 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[11px]">Suspender</span>
                        </button>

                        <button
                            wire:click="aplicarDictamen('ELIMINADO')"
                            onclick="return confirm('¿Seguro que deseas eliminar esta publicación? Esta acción no se puede deshacer.')"
                            class="flex flex-col items-center gap-1.5 p-3 rounded-2xl border-2 border-rose-200 bg-rose-50 text-rose-700 font-bold hover:bg-rose-100 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-[11px]">Eliminar</span>
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>