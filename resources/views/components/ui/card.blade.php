{{-- size: default (gap-6 py-6, px-6 content) | sm (gap-4 py-4, px-4 content) | flush (no padding, for custom layouts) --}}
@props(['size' => 'default'])

@php
    $sizes = [
        'default' => 'gap-6 py-6',
        'sm' => 'gap-4 py-4',
        'flush' => '',
    ];
@endphp

<div data-size="{{ $size }}" {{ $attributes->merge(['class' => 'group/card flex flex-col rounded-xl border bg-card text-card-foreground shadow-sm '.($sizes[$size] ?? $sizes['default'])]) }}>{{ $slot }}</div>
