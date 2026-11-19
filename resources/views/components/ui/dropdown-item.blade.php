@props(['href' => null, 'active' => false, 'destructive' => false, 'disabled' => false])

@php
    $classes = 'flex w-full cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-left text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:bg-accent disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-4 [&_svg]:shrink-0 '
        .($destructive ? 'text-destructive hover:bg-destructive/10 hover:text-destructive ' : '')
        .($active ? 'bg-accent font-medium' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="button" @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
