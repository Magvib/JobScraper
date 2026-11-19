@props(['size' => 'size-4'])

<x-ui.icon name="loader" :size="$size" {{ $attributes->merge(['class' => 'animate-spin']) }} />
