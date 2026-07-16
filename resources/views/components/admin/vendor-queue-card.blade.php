@props(['vendedor'])

<div class="min-w-[300px] max-w-[320px] bg-white p-4 rounded-2xl shadow-sm border border-neutral-100 flex gap-3 items-center">
    <img
        src="{{ $vendedor['imagen'] ?? 'https://ui-avatars.com/api/?name=V&background=D81B60&color=fff' }}"
        alt="{{ $vendedor['tienda'] ?? 'Vendedor' }}"
        class="w-16 h-16 rounded-xl object-cover shrink-0 border border-neutral-100" />
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-bold text-[#D81B60] truncate">{{ $vendedor['tienda'] ?? 'Sin tienda' }}</h4>
        <p class="text-xs text-neutral-400 mb-1 truncate">Prop: {{ $vendedor['propietario'] ?? '—' }}</p>
        <p class="text-[10px] text-neutral-500 mb-1.5 truncate">{{ ucfirst($vendedor['estatus'] ?? '') }} · {{ $vendedor['codigo_ine'] ?? '' }}</p>
        <button
            type="button"
            wire:click="$dispatch('abrirVendedor', { id: {{ (int) ($vendedor['id'] ?? 0) }} })"
            class="w-full bg-[#D81B60] text-white text-xs font-semibold py-2 rounded-full hover:bg-[#b0124a] transition">
            Revisar
        </button>
    </div>
</div>
