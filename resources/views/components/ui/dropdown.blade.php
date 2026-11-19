{{--
    Click-to-open menu. Put the trigger in the `trigger` slot and <x-ui.dropdown-item>s in the default slot.
    align: start | end   side: bottom | top
--}}
@props(['align' => 'end', 'side' => 'bottom', 'width' => 'w-56'])

<div
    x-data="{ open: false }"
    x-on:keydown.escape.window="open = false"
    x-on:click.outside="open = false"
    class="relative inline-block"
>
    <div x-on:click="open = !open" x-bind:aria-expanded="open">{{ $trigger }}</div>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        x-on:click="open = false"
        {{ $attributes->merge(['class' => 'absolute z-50 '.$width.' overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md '.($side === 'top' ? 'bottom-full mb-2 ' : 'top-full mt-2 ').($align === 'start' ? 'left-0' : 'right-0')]) }}
    >
        {{ $slot }}
    </div>
</div>
