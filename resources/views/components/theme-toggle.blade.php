{{-- Light/dark switch. The choice lives in localStorage; the head partial applies it before first paint. --}}
<x-ui.button
    variant="ghost"
    size="icon"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    x-on:theme-changed.window="dark = $event.detail === 'dark'"
    x-on:click="window.setTheme(dark ? 'light' : 'dark')"
    aria-label="{{ __('Toggle theme') }}"
    title="{{ __('Toggle theme') }}"
>
    <x-ui.icon name="sun" x-show="dark" x-cloak />
    <x-ui.icon name="moon" x-show="!dark" />
</x-ui.button>
