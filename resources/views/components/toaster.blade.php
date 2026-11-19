{{-- Listens for the `toast` browser event: $this->dispatch('toast', message: '...', type: 'success|error|warning|info') --}}
<div
    x-data="toastManager()"
    x-on:toast.window="add($event.detail)"
    class="pointer-events-none fixed bottom-4 right-4 z-[1000] flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-2"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.show"
            x-transition.opacity
            class="pointer-events-auto flex animate-toast-in items-start gap-3 rounded-lg border bg-popover p-4 text-sm text-popover-foreground shadow-lg"
        >
            <span
                class="mt-0.5 size-2 shrink-0 rounded-full"
                :class="{
                    'bg-success': toast.type === 'success',
                    'bg-destructive': toast.type === 'error',
                    'bg-warning': toast.type === 'warning',
                    'bg-info': toast.type === 'info',
                }"
            ></span>
            <span class="grow" x-text="toast.message"></span>
            <button type="button" class="-mr-1 -mt-1 shrink-0 rounded-sm p-1 text-muted-foreground transition-colors hover:text-foreground" @click="remove(toast.id)" aria-label="{{ __('Dismiss') }}">
                <x-ui.icon name="x" size="size-3.5" />
            </button>
        </div>
    </template>
</div>

<script>
    function toastManager() {
        return {
            toasts: [],

            add(toast) {
                const id = Date.now() + Math.random();

                this.toasts.push({
                    id,
                    message: toast.message,
                    type: toast.type || 'info',
                    show: true,
                });

                setTimeout(() => this.remove(id), 4000);
            },

            remove(id) {
                const index = this.toasts.findIndex((t) => t.id === id);

                if (index !== -1) {
                    this.toasts.splice(index, 1);
                }
            },
        };
    }
</script>
