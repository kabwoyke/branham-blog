<div wire:poll.30s.visible="checkForChanges">
    <h1 class="text-3xl font-bold">Site settings</h1>

    @if ($hasNewerSettings)
        <div class="mt-6 flex max-w-3xl flex-wrap items-center justify-between gap-3 rounded-lg border-l-4 border-amber-500 bg-amber-50 px-4 py-3 text-sm text-amber-950" role="status">
            <p>Settings were saved somewhere else (another tab or device) since you opened this page.</p>
            <button type="button" wire:click="loadSettings" wire:confirm="Load the latest saved settings? Unsaved changes on this page will be replaced." class="rounded-lg bg-amber-500 px-3 py-1.5 font-semibold text-ink hover:bg-amber-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">Load latest</button>
        </div>
    @endif

    <form wire:submit="save" class="mt-8 max-w-3xl space-y-6">
        @if ($errors->any())
            <div class="rounded-lg border-l-4 border-red-700 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                <p class="font-semibold">Settings were not saved. Please fix the following:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-admin.input label="Site name" name="site_name" wire:model="site_name" required />
        <x-admin.input label="Tagline" name="tagline" wire:model="tagline" />

        <fieldset class="space-y-4 card p-4">
            <legend class="px-1 text-sm font-semibold uppercase tracking-wide">Contact details</legend>
            <x-admin.input label="Email" name="contact_email" type="email" wire:model="contact_email" required />
            <x-admin.input label="Phone / WhatsApp" name="contact_phone" wire:model="contact_phone" hint="Include the country code, e.g. +254…" />
        </fieldset>

        <fieldset class="space-y-4 card p-4">
            <legend class="px-1 text-sm font-semibold uppercase tracking-wide">Social media</legend>
            <p class="text-xs text-muted">Paste the full address of each profile. Leave a box empty to hide that platform. Filled-in profiles appear in the site footer and on the Contact page.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($socialProfiles as $key => $profile)
                    <x-admin.input :label="$profile['label']" :name="$key" type="url" wire:model="{{ $key }}" :placeholder="$profile['example']" />
                @endforeach
            </div>
        </fieldset>

        <x-admin.markdown-editor label="About page content" model="about_content" />

        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-brand px-5 py-3 shadow-sm text-sm font-medium text-white hover:bg-brand/90 disabled:opacity-60">
            <span wire:loading.remove wire:target="save">Save settings</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </form>
</div>
