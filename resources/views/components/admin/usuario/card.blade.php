@props([
'title',
'value',
'subtitle',
'subtitleIcon' => null,
'subtitleColor' => 'text-gray-500',
'valueColor' => 'text-gray-900',
'icon',
'iconColor' => 'text-gray-600',
'iconBg' => 'bg-gray-100'
])

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100/80 flex flex-col justify-between h-full">
    <div class="flex justify-between items-start mb-4">
        <span class="text-gray-500 text-xs font-bold uppercase tracking-wider">{{ $title }}</span>
        <div class="{{ $iconBg }} p-2 rounded-full flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px] {{ $iconColor }}">{{ $icon }}</span>
        </div>
    </div>
    <div>
        <h3 class="text-3xl font-bold {{ $valueColor }}">{{ $value }}</h3>
        <p class="{{ $subtitleColor }} text-xs font-medium flex items-center gap-1 mt-2">
            @if($subtitleIcon)
            <span class="material-symbols-outlined text-[16px]">{{ $subtitleIcon }}</span>
            @endif
            {{ $subtitle }}
        </p>
    </div>
</div>