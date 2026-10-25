<div>
    <h1 class="text-2xl font-bold sm:text-3xl">Comments</h1>

    <div class="mt-4 max-w-xs">
        <x-admin.select label="Show" name="filter" wire:model.live="filter">
            <option value="pending">Awaiting approval</option>
            <option value="approved">Approved</option>
            <option value="all">All</option>
        </x-admin.select>
    </div>

    <ul class="mt-6 divide-y divide-muted/20 card">
        @forelse ($comments as $comment)
            <li class="px-4 py-3" wire:key="comment-{{ $comment->id }}">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div class="min-w-0">
                        <p class="text-xs text-muted">
                            {{ $comment->name }} &lt;{{ $comment->email }}&gt; · {{ $comment->created_at->format('j M Y, H:i') }} ·
                            <a href="{{ route('articles.show', $comment->article->slug) }}" class="text-brand hover:underline">{{ $comment->article->title }}</a>
                        </p>
                        <p class="mt-1 whitespace-pre-line break-words text-sm">{{ $comment->body }}</p>
                    </div>
                    <div class="flex shrink-0 gap-3 text-sm">
                        @if ($comment->isApproved())
                            <button type="button" wire:click="unapprove({{ $comment->id }})" class="text-brand hover:underline">Unapprove</button>
                        @else
                            <button type="button" wire:click="approve({{ $comment->id }})" class="text-brand hover:underline">Approve</button>
                        @endif
                        <button type="button" wire:click="delete({{ $comment->id }})" wire:confirm="Delete this comment?" class="text-red-700 hover:underline">Delete</button>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-6 text-center text-sm text-muted">No comments.</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $comments->links() }}</div>
</div>
