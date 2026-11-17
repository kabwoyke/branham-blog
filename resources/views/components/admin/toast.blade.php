<div
    x-data="{
        show: false,
        message: '',
        type: 'status',
        timer: null,
        notify(detail) {
            this.message = detail.message
            this.type = detail.type === 'error' ? 'error' : 'status'
            this.show = true
            clearTimeout(this.timer)
            this.timer = setTimeout(() => { this.show = false }, this.type === 'error' ? 8000 : 4000)
        },
    }"
    x-on:notify.window="notify($event.detail)"
    class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex justify-center sm:inset-x-auto sm:right-6 sm:bottom-6"
    role="status"
    aria-live="polite"
>
    <div
        x-cloak
        x-show="show"
        x-transition.opacity.duration.200ms
        x-bind:class="type === 'error' ? 'border-red-700 bg-red-50 text-red-900' : 'border-brand bg-white text-ink'"
        class="pointer-events-auto flex max-w-md items-start gap-3 rounded-lg border-l-4 px-4 py-3 text-sm shadow-lg"
    >
        <span x-text="message"></span>
        <button type="button" x-on:click="show = false" class="ml-auto text-muted hover:text-ink" aria-label="Dismiss">&times;</button>
    </div>
</div>
