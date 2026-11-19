{{-- Empty state: icon + title + description, with optional actions in the default slot. --}}
@props(['icon' => 'search', 'title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed px-6 py-14 text-center']) }}>
    <div class="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground">
        <x-ui.icon :name="$icon" size="size-6" />
    </div>
    @if ($title)
        <h3 class="text-lg font-semibold tracking-tight">{{ $title }}</h3>
    @endif
    @if ($description)
        <p class="max-w-md text-sm text-muted-foreground">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-1">{{ $slot }}</div>
    @endif
</div>
