@props(['vendedor'])

<div class="min-w-[300px] bg-white p-4 rounded-2xl shadow-sm border border-neutral-100 flex gap-3 items-center">
    <img src="{{ $vendedor['imagen'] }}" alt="{{ $vendedor['tienda'] }}" class="w-16 h-16 rounded-xl object-cover shrink-0" />
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold text-[#D81B60] truncate">{{ $vendedor['tienda'] }}</h4>
        <p class="text-xs text-neutral-400 mb-2 truncate">Prop: {{ $vendedor['propietario'] }}</p>
        <button
            wire:click="$dispatch('abrirVendedor', { id: {{ $vendedor['id'] }} })"
            class="w-full bg-[#D81B60] text-white text-xs font-semibold py-2 rounded-full hover:bg-[#b0124a] transition">
            Revisar
        </button>
    </div>
</div>