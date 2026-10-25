<div>
    <h1 class="text-2xl font-bold sm:text-3xl">Messages</h1>

    <label class="mt-4 flex items-center gap-2 text-sm">
        <input type="checkbox" wire:model.live="unreadOnly" class="size-4 accent-brand">
        Unread only
    </label>

    <ul class="mt-6 divide-y divide-muted/20 card">
        @forelse ($messages as $message)
            <li class="px-4 py-3" wire:key="message-{{ $message->id }}">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <button type="button" wire:click="open({{ $message->id }})" class="min-w-0 break-words text-left"aria-expanded="{{ $openId === $message->id ? 'true' : 'false' }}">
                        <span @class(['block text-sm', 'font-semibold' => ! $message->isRead()])>
                            @unless ($message->isRead())<span class="mr-1 text-brand" aria-label="Unread">●</span>@endunless{{ $message->subject }}
                        </span>
                        <span class="block text-xs text-muted">{{ $message->name }} &lt;{{ $message->email }}&gt; · {{ $message->created_at->format('j M Y, H:i') }}</span>
                    </button>
                    <div class="flex shrink-0 gap-3 text-sm">
                        @if ($message->isRead())
                            <button type="button" wire:click="markUnread({{ $message->id }})" class="text-brand hover:underline">Mark unread</button>
                        @else
                            <button type="button" wire:click="markRead({{ $message->id }})" class="text-brand hover:underline">Mark read</button>
                        @endif
                        <button type="button" wire:click="delete({{ $message->id }})" wire:confirm="Delete this message?" class="text-red-700 hover:underline">Delete</button>
                    </div>
                </div>

                @if ($openId === $message->id)
                    <div class="mt-3 whitespace-pre-line border-l-4 border-brand bg-paper px-4 py-3 text-sm">{{ $message->message }}</div>
                @endif
            </li>
        @empty
            <li class="px-4 py-6 text-center text-sm text-muted">No messages.</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $messages->links() }}</div>
</div>
