{{-- Label + control + hint + validation error. `error` is the validation key, e.g. error="email". --}}
@props(['label' => null, 'for' => null, 'hint' => null, 'error' => null])

<div {{ $attributes->merge(['class' => 'grid gap-2']) }}>
    @if ($label)
        <x-ui.label :for="$for">{{ $label }}</x-ui.label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-muted-foreground">{{ $hint }}</p>
    @endif

    @if ($error)
        @error($error)
            <p class="text-xs font-medium text-destructive">{{ $message }}</p>
        @enderror
    @endif
</div>
