{{-- Page title + description on the left, actions (default slot) on the right. --}}
@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="min-w-0 space-y-1">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
        @isset($subtitle)
            <div class="text-sm text-muted-foreground">{{ $subtitle }}</div>
        @elseif ($description)
            <p class="text-sm text-muted-foreground">{{ $description }}</p>
        @endisset
    </div>

    @if (! $slot->isEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
