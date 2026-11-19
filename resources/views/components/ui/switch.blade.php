{{-- Toggle switch backed by a real checkbox, so wire:model / x-model work as usual. --}}
<label class="relative inline-flex shrink-0 cursor-pointer items-center">
    <input type="checkbox" {{ $attributes->merge(['class' => 'peer sr-only']) }} />
    <span class="h-[1.15rem] w-8 rounded-full border border-transparent bg-input shadow-xs transition-colors peer-checked:bg-primary peer-focus-visible:ring-[3px] peer-focus-visible:ring-ring/50 peer-disabled:opacity-50 dark:bg-input/80"></span>
    <span class="pointer-events-none absolute left-0.5 top-1/2 size-4 -translate-y-1/2 rounded-full bg-background shadow transition-transform peer-checked:translate-x-[0.9rem] dark:bg-foreground dark:peer-checked:bg-primary-foreground"></span>
</label>
