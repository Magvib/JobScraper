{{-- Coloured percentage badge: 80+ green, 50-79 amber, below 50 red. --}}
@props(['value', 'label' => null])

@php
    $score = (float) $value;
    $variant = $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'destructive');
@endphp

<x-ui.badge :variant="$variant" {{ $attributes->merge(['class' => 'tabular-nums']) }}>
    @if ($label)
        <span class="font-normal opacity-70">{{ $label }}</span>
    @endif
    {{ number_format($score, 0) }}%
</x-ui.badge>
