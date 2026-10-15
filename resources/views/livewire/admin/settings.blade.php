<div>
    <h1 class="text-3xl font-bold">Site settings</h1>

    <form wire:submit="save" class="mt-8 max-w-3xl space-y-6">
        <x-admin.input label="Site name" name="site_name" wire:model="site_name" required />
        <x-admin.input label="Tagline" name="tagline" wire:model="tagline" />

        <fieldset class="space-y-4 card p-4">
            <legend class="px-1 text-sm font-semibold uppercase tracking-wide">Contact details</legend>
            <x-admin.input label="Email" name="contact_email" type="email" wire:model="contact_email" required />
            <x-admin.input label="Phone / WhatsApp" name="contact_phone" wire:model="contact_phone" hint="Include the country code, e.g. +254…" />
            <x-admin.input label="LinkedIn URL" name="linkedin_url" type="url" wire:model="linkedin_url" placeholder="https://www.linkedin.com/in/…" />
        </fieldset>

        <x-admin.markdown-editor label="About page content" model="about_content" />

        <button type="submit" class="rounded-lg bg-brand px-5 py-3 shadow-sm text-sm font-medium text-white hover:bg-brand/90">Save settings</button>
    </form>
</div>
