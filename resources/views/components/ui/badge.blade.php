@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'border-transparent bg-primary text-primary-foreground',
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground',
        'outline' => 'text-foreground',
        'muted' => 'border-transparent bg-muted text-muted-foreground',
        'destructive' => 'border-transparent bg-destructive/10 text-destructive',
        'success' => 'border-transparent bg-success/10 text-success',
        'warning' => 'border-transparent bg-warning/10 text-warning',
        'info' => 'border-transparent bg-info/10 text-info',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex w-fit shrink-0 items-center justify-center gap-1 whitespace-nowrap rounded-md border px-2 py-0.5 text-xs font-medium [&>svg]:pointer-events-none [&>svg]:size-3 '.($variants[$variant] ?? $variants['default'])]) }}>{{ $slot }}</span>
