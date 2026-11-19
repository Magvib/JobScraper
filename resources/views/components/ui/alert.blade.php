@props(['variant' => 'default', 'icon' => 'info'])

@php
    $variants = [
        'default' => 'bg-card text-card-foreground',
        'destructive' => 'border-destructive/30 bg-destructive/5 text-destructive',
        'warning' => 'border-warning/30 bg-warning/5 text-warning',
        'success' => 'border-success/30 bg-success/5 text-success',
    ];
@endphp

<div role="alert" {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm '.($variants[$variant] ?? $variants['default'])]) }}>
    <x-ui.icon :name="$icon" class="mt-0.5" />
    <div class="grid gap-0.5">{{ $slot }}</div>
</div>
