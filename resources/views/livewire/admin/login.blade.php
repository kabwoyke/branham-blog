<div class="mx-auto max-w-sm py-12">
    <h1 class="text-3xl font-semibold">Sign in</h1>
    <p class="mt-2 text-sm text-muted">Admin area for {{ config('app.name') }}.</p>

    <form wire:submit="login" class="mt-8 space-y-5">
        <x-admin.input label="Email" name="email" type="email" wire:model="email" autocomplete="username" required autofocus />
        <x-admin.input label="Password" name="password" type="password" wire:model="password" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="remember" class="size-4 accent-brand">
            Remember me
        </label>

        <button type="submit" class="w-full bg-brand px-5 py-3 text-sm font-medium text-white hover:bg-brand/90" wire:loading.attr="disabled">Sign in</button>
    </form>
</div>
