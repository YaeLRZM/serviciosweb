<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

@props([
    'label',
    'value',
    'trend' => null,
    'trendColor' => 'text-neutral-400',
    'iconBg' => 'bg-neutral-100',
    'iconColor' => 'text-neutral-500',
    'borderColor' => null,
    'active' => false,
    'clickable' => false,
])

@php
    $base = 'bg-white rounded-2xl shadow-sm p-5 flex items-start justify-between w-full text-left transition '
        . ($borderColor ? 'border border-l-4 '.$borderColor : 'border border-neutral-100');
    if ($active) {
        $base .= ' ring-2 ring-[#D81B60]/50 shadow-md bg-rose-50/80';
    }
    if ($clickable) {
        $base .= ' cursor-pointer hover:shadow-md hover:border-[#D81B60]/30';
    }
@endphp

@if ($clickable)
<button type="button" {{ $attributes->merge(['class' => $base]) }}>
@else
<div {{ $attributes->merge(['class' => $base]) }}>
@endif
    <div>
        <div class="text-xs font-medium {{ $active ? 'text-[#D81B60]' : 'text-neutral-400' }} uppercase tracking-wide">{{ $label }}</div>
        <div class="text-3xl font-bold text-neutral-900 mt-2">{{ $value }}</div>
        @if ($trend)
        <div class="text-xs font-medium {{ $trendColor }} mt-1">{{ $trend }}</div>
        @endif
    </div>
    <div class="w-10 h-10 rounded-full {{ $iconBg }} {{ $iconColor }} flex items-center justify-center shrink-0">
        {{ $slot }}
    </div>
@if ($clickable)
</button>
@else
</div>
@endif
