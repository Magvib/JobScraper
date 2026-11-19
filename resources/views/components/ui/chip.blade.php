{{-- Pill used for filters. `active` renders the selected state. --}}
@props(['active' => false])

<button type="button" aria-pressed="{{ $active ? 'true' : 'false' }}" {{ $attributes->merge(['class' => 'inline-flex h-7 shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border px-3 text-xs font-medium outline-none transition-colors focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50 '.($active ? 'border-transparent bg-primary text-primary-foreground' : 'bg-background text-muted-foreground hover:bg-accent hover:text-accent-foreground dark:bg-input/30')]) }}>{{ $slot }}</button>
