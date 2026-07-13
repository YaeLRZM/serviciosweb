@props(['show' => false, 'maxWidth' => 'md', 'title' => null, 'subtitle' => null])

@php
$maxWidthClass = [
'sm' => 'max-w-sm',
'md' => 'max-w-md',
'lg' => 'max-w-lg',
'xl' => 'max-w-xl',
'2xl' => 'max-w-2xl',
][$maxWidth] ?? 'max-w-md';
@endphp

@if ($show)
<div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 {{ $maxWidthClass }} w-full p-6 overflow-hidden transform transition-all">

        @if ($title)
        <div class="flex items-start justify-between mb-1">
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
                @if ($subtitle)
                <p class="text-xs text-gray-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>

            {{ $closeButton ?? '' }}
        </div>
        @endif

        <div class="mt-5">
            {{ $slot }}
        </div>
    </div>
</div>
@endif