{{-- Native <select> with shadcn styling. Attributes (wire:model etc.) land on the select. --}}
<div class="relative w-full">
    <select {{ $attributes->merge(['class' => 'h-9 w-full appearance-none rounded-md border border-input bg-transparent py-1 pl-3 pr-9 text-sm shadow-xs outline-none transition-[color,box-shadow] disabled:cursor-not-allowed disabled:opacity-50 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive dark:bg-input/30 dark:[&>option]:bg-popover']) }}>{{ $slot }}</select>
    <x-ui.icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground opacity-70" />
</div>
