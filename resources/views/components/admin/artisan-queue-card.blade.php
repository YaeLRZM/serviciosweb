@props(['artesano'])

@php
$colores = [
'revision' => ['dot' => 'bg-amber-500', 'text' => 'text-amber-600'],
'nueva' => ['dot' => 'bg-neutral-400', 'text' => 'text-neutral-500'],
'documentos' => ['dot' => 'bg-amber-500', 'text' => 'text-amber-600'],
];
$estilo = $colores[$artesano['estado']] ?? $colores['nueva'];
@endphp

<div class="bg-white/70 backdrop-blur-md border border-white/60 p-5 rounded-2xl flex flex-col gap-4 shadow-sm hover:border-[#D81B60]/40 transition-all">
    <div class="flex items-center gap-3">
        <img src="{{ $artesano['foto'] }}" alt="{{ $artesano['nombre'] }}" class="w-12 h-12 rounded-full object-cover" />
        <div>
            <h4 class="text-sm font-bold text-neutral-800">{{ $artesano['nombre'] }}</h4>
            <p class="text-[11px] text-neutral-400 uppercase tracking-wider">{{ $artesano['especialidad'] }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full {{ $estilo['dot'] }}"></span>
        <span class="text-xs font-semibold {{ $estilo['text'] }}">{{ $artesano['estadoLabel'] }}</span>
    </div>

    <button
        wire:click="$dispatch('abrirRevisionArtesano', { id: {{ $artesano['id'] }} })"
        class="mt-1 w-full py-2 bg-white border border-neutral-200 text-[#D81B60] rounded-lg text-sm font-semibold hover:bg-[#D81B60] hover:text-white hover:border-[#D81B60] transition-colors">
        {{ $artesano['accionLabel'] }}
    </button>
</div>