@props([
'icon' => 'home',
'ruta' => '#',
'texto' => '',
'disabled' => false,
])

@php
// Si la ruta es '#' o no existe aún, evitamos que rompa routeIs o route()
$isCurrent = ($ruta !== '#') ? request()->routeIs($ruta) : false;
$url = ($ruta !== '#' && !$disabled) ? route($ruta) : '#';

// Mapeo dinámico de tus iconos personalizados para no usar la directiva rota de Flux
$iconComponent = "icon." . $icon;
@endphp

@if($isCurrent)
<!-- ESTADO SELECCIONADO: Fondo Blanco Marfil (#F8F5F2) y letras Rosa Bugambilia (#D81B60) -->
<a href="{{ $url }}"
    {{ $disabled ? 'disabled' : '' }}
    @if(!$isCurrent && !$disabled) wire:navigate @endif
    class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#F8F5F2] text-[#D81B60] font-medium shadow-sm transition-all duration-300 transform translate-x-1">
    <x-dynamic-component :component="$iconComponent" variant="mini" />
    <span class="text-sm font-semibold tracking-wide">{{ __($texto) }}</span>
</a>
@else
<!-- ESTADO INACTIVO: Texto Blanco Marfil (#F8F5F2) con un sutil hover traslúcido -->
<a href="{{ $url }}"
    {{ $disabled ? 'disabled' : '' }}
    @if(!$isCurrent && !$disabled) wire:navigate @endif
    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#F8F5F2] hover:bg-[#F8F5F2]/10 hover:translate-x-1.5 font-medium transition-all duration-300 @if($disabled) opacity-80 cursor-not-allowed @endif">
    <x-dynamic-component :component="$iconComponent" variant="mini" />
    <span class="text-sm tracking-wide">{{ __($texto) }}</span>
</a>
@endif