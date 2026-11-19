@props(['label', 'value', 'hint' => null, 'tone' => null])

@php
    $toneClass = match ($tone) {
        'success' => 'text-success',
        'warning' => 'text-warning',
        'destructive' => 'text-destructive',
        'info' => 'text-info',
        default => 'text-foreground',
    };
@endphp

<x-ui.card size="sm" {{ $attributes }}>
    <x-ui.card-content class="space-y-1">
        <p class="text-sm font-medium text-muted-foreground">{{ $label }}</p>
        <p class="text-3xl font-semibold tracking-tight tabular-nums {{ $toneClass }}">{{ $value }}</p>
        @if ($hint)
            <p class="text-xs text-muted-foreground">{{ $hint }}</p>
        @endif
    </x-ui.card-content>
</x-ui.card>
