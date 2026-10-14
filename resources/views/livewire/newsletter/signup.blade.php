<div>
    @if ($submitted)
        <p class="font-semibold text-brand" role="status">Check your inbox for a confirmation link to finish subscribing.</p>
    @else
        <form wire:submit="subscribe" class="flex flex-col gap-3 sm:flex-row" novalidate>
            <div class="flex-1">
                <label for="newsletter-email-{{ $location }}" class="sr-only">Email address</label>
                <input
                    id="newsletter-email-{{ $location }}"
                    type="email"
                    wire:model="email"
                    placeholder="Your email address"
                    autocomplete="email"
                    required
                    class="block w-full rounded-lg border border-muted/40 bg-white px-4 py-3 text-base"
                    @error('email') aria-invalid="true" aria-describedby="newsletter-error-{{ $location }}" @enderror
                >
                @error('email') <p id="newsletter-error-{{ $location }}" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
            </div>

            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="newsletter-website-{{ $location }}">Leave this field empty</label>
                <input id="newsletter-website-{{ $location }}" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="subscribe">Subscribe</button>
        </form>
    @endif
</div>
