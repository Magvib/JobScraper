{{-- CSS-only tooltip. Shows on hover and keyboard focus; renders nothing extra when `text` is empty. --}}
@props(['text' => null, 'side' => 'top'])

<span {{ $attributes->merge(['class' => 'group/tip relative inline-flex']) }}>
    {{ $slot }}
    @if (filled($text))
        <span role="tooltip" class="pointer-events-none absolute left-1/2 z-50 w-max max-w-64 -translate-x-1/2 whitespace-normal rounded-md bg-foreground px-3 py-1.5 text-center text-xs font-normal text-background opacity-0 shadow-md transition-opacity group-focus-within/tip:opacity-100 group-hover/tip:opacity-100 {{ $side === 'bottom' ? 'top-full mt-1.5' : 'bottom-full mb-1.5' }}">{{ $text }}</span>
    @endif
</span>
