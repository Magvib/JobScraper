{{--
    Modal dialog. Render it conditionally (@if) from the server, or wrap it in an Alpine x-if/x-show.
    `close` is an Alpine expression run on Escape / backdrop click, e.g. close="$wire.closeModal()".
--}}
@props(['close' => null, 'maxWidth' => 'max-w-3xl', 'padding' => 'p-6'])

<div
    x-data
    @if ($close) x-on:keydown.escape.window="{{ $close }}" @endif
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
>
    <div class="fixed inset-0 animate-fade-in bg-black/60 backdrop-blur-[2px]" @if ($close) x-on:click="{{ $close }}" @endif></div>

    <div {{ $attributes->merge(['class' => 'relative z-10 flex max-h-[92vh] w-full animate-dialog-in flex-col gap-4 overflow-hidden rounded-lg border bg-background shadow-lg '.$padding.' '.$maxWidth]) }}>
        {{ $slot }}
    </div>
</div>
