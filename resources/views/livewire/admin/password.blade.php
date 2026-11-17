<div>
    <h1 class="text-3xl font-bold">Change password</h1>

    <form wire:submit="save" class="mt-8 max-w-xl space-y-6">
        <x-admin.input label="Current password" name="current_password" type="password" wire:model="current_password" autocomplete="current-password" required />
        <x-admin.input label="New password" name="password" type="password" wire:model="password" autocomplete="new-password" hint="At least 10 characters." required />
        <x-admin.input label="Confirm new password" name="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required />

        <button type="submit" class="rounded-lg bg-brand px-5 py-3 shadow-sm text-sm font-medium text-white hover:bg-brand/90">Update password</button>
    </form>
</div>
