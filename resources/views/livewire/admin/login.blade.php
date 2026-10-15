<div>
    <p class="eyebrow">Admin area</p>
    <h1 class="mt-2 text-3xl font-bold">Welcome back</h1>
    <p class="mt-2 text-sm text-muted">Sign in to write and manage articles for {{ config('app.name') }}.</p>

    <form wire:submit="login" class="mt-8 space-y-5">
        <x-admin.input label="Email" name="email" type="email" wire:model="email" autocomplete="username" required autofocus />

        <div x-data="{ visible: false }">
            <label for="password" class="block text-sm font-medium">Password</label>
            <div class="relative mt-1">
                <input id="password" name="password" wire:model="password" x-bind:type="visible ? 'text' : 'password'" type="password" autocomplete="current-password" required
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    class="block w-full rounded-lg border border-muted/40 bg-white py-2 pl-3 pr-16 text-sm">
                <button type="button" x-on:click="visible = ! visible" x-bind:aria-pressed="visible" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-muted hover:text-brand">
                    <span x-text="visible ? 'Hide' : 'Show'">Show</span>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="remember" class="size-4 accent-brand">
            Keep me signed in on this device
        </label>

        <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:loading.class="opacity-70">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
        </button>
    </form>
</div>
