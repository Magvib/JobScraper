{{-- Logo mark + wordmark. The mark is the same artwork as public/favicon.svg, recoloured from the theme tokens. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 font-semibold tracking-tight']) }}>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" class="size-7 shrink-0" aria-hidden="true">
        <rect width="64" height="64" rx="14" class="fill-primary" />
        <g fill="none" class="stroke-primary-foreground" stroke-linecap="round">
            <path d="M44 11v23c0 9-5.5 14-13 14" stroke-width="8" />
            <circle cx="23" cy="40" r="9.5" stroke-width="7.5" />
            <path d="M30.5 47.5l8.5 8.5" stroke-width="7.5" />
        </g>
    </svg>
    <span>Job<span class="text-muted-foreground">Scraper</span></span>
</span>
