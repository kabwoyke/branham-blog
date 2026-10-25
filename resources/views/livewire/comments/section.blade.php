<section aria-labelledby="comments-heading">
    <h2 id="comments-heading" class="text-xl font-bold">Comments @if ($comments->isNotEmpty())<span class="text-muted">({{ $comments->count() }})</span>@endif</h2>

    <ul class="mt-6 space-y-6">
        @forelse ($comments as $comment)
            <li wire:key="comment-{{ $comment->id }}">
                <p class="text-sm"><span class="font-semibold">{{ $comment->name }}</span> <span class="text-muted">· {{ $comment->created_at->format('j M Y') }}</span></p>
                <p class="mt-1 whitespace-pre-line break-words">{{ $comment->body }}</p>
            </li>
        @empty
            <li class="text-muted">No comments yet. Be the first to share a thought.</li>
        @endforelse
    </ul>

    <div class="mt-10">
        @if ($submitted)
            <div class="border-l-4 border-brand bg-brand/10 px-4 py-3" role="status">
                Thank you. Your comment will appear once it has been approved.
            </div>
        @else
            <h3 class="text-lg font-semibold">Leave a comment</h3>
            <form wire:submit="submit" class="mt-4 space-y-4" novalidate>
                @php($field = 'block w-full rounded-lg border border-muted/40 bg-white px-4 py-3 text-base')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="comment-name" class="mb-1 block text-sm font-semibold">Name</label>
                        <input id="comment-name" type="text" wire:model="name" autocomplete="name" required class="{{ $field }}" @error('name') aria-invalid="true" aria-describedby="comment-name-error" @enderror>
                        @error('name') <p id="comment-name-error" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="comment-email" class="mb-1 block text-sm font-semibold">Email <span class="font-normal text-muted">(never shown)</span></label>
                        <input id="comment-email" type="email" wire:model="email" autocomplete="email" required class="{{ $field }}" @error('email') aria-invalid="true" aria-describedby="comment-email-error" @enderror>
                        @error('email') <p id="comment-email-error" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="comment-body" class="mb-1 block text-sm font-semibold">Comment</label>
                    <textarea id="comment-body" rows="5" wire:model="body" required class="{{ $field }}" @error('body') aria-invalid="true" aria-describedby="comment-body-error" @enderror></textarea>
                    @error('body') <p id="comment-body-error" class="mt-1 text-sm text-red-800">{{ $message }}</p> @enderror
                </div>

                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label for="comment-website">Leave this field empty</label>
                    <input id="comment-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                </div>

                <p class="text-sm text-muted">Comments are reviewed before they appear.</p>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Post comment</span>
                    <span wire:loading wire:target="submit">Posting…</span>
                </button>
            </form>
        @endif
    </div>
</section>
