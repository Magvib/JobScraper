{{-- Image with an initials fallback underneath (the image removes itself if it fails to load). `square` gives rounded corners, for company logos. --}}
@props(['src' => null, 'name' => '', 'size' => 'size-9', 'square' => false])

@php
    $initials = collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<span {{ $attributes->merge(['class' => 'relative flex shrink-0 select-none items-center justify-center overflow-hidden bg-muted text-xs font-medium text-muted-foreground '.($square ? 'rounded-lg border ' : 'rounded-full ').$size]) }}>
    {{ $initials }}
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" class="absolute inset-0 size-full {{ $square ? 'bg-white object-contain p-1' : 'object-cover' }}" loading="lazy" referrerpolicy="no-referrer" onerror="this.remove()">
    @endif
</span>
