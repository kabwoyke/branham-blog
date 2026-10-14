<div>
    @if ($sent)
        <div class="border-l-4 border-brand bg-brand/10 px-4 py-3" role="status">
            Thank you. Your message has been sent and you will hear back by email.
        </div>
    @else
        <form wire:submit="send" class="space-y-5" novalidate>
            @php($field = 'block w-full rounded-lg border border-muted/40 bg-white px-4 py-3 text-base')
            <div>
                <label for="contact-name" class="mb-1 block text-sm font-semibold">Name</label>
                <input id="contact-name" type="text" wire:model="name" autocomplete="name" required class="{{ $field }}" @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                @error('name') <p id="contact-name-error" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contact-email" class="mb-1 block text-sm font-semibold">Email</label>
                <input id="contact-email" type="email" wire:model="email" autocomplete="email" required class="{{ $field }}" @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                @error('email') <p id="contact-email-error" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contact-subject" class="mb-1 block text-sm font-semibold">Subject</label>
                <input id="contact-subject" type="text" wire:model="subject" required class="{{ $field }}" @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>
                @error('subject') <p id="contact-subject-error" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contact-message" class="mb-1 block text-sm font-semibold">Message</label>
                <textarea id="contact-message" rows="7" wire:model="message" required class="{{ $field }}" @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror></textarea>
                @error('message') <p id="contact-message-error" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
            </div>

            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="contact-website">Leave this field empty</label>
                <input id="contact-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="send">Send message</span>
                <span wire:loading wire:target="send">Sending…</span>
            </button>
        </form>
    @endif
</div>
