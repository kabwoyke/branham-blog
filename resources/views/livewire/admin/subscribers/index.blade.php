<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold sm:text-3xl">Subscribers</h1>
        <a href="{{ route('admin.subscribers.export') }}" class="rounded-lg border border-brand px-4 py-2 text-sm font-medium text-brand hover:bg-brand/10">Export CSV</a>
    </div>
    <p class="mt-2 text-sm text-muted">{{ $confirmedCount }} confirmed.</p>

    <div class="mt-6 max-w-sm">
        <x-admin.input label="Search email" name="search" type="search" wire:model.live.debounce.300ms="search" />
    </div>

    <div class="mt-6 overflow-x-auto card">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-muted/20 text-xs uppercase tracking-wide text-muted">
                <tr>
                    <th scope="col" class="px-4 py-3">Email</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="hidden px-4 py-3 sm:table-cell">Subscribed</th>
                    <th scope="col" class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-muted/20">
                @forelse ($subscribers as $subscriber)
                    <tr wire:key="subscriber-{{ $subscriber->id }}">
                        <td class="break-all px-4 py-3">{{ $subscriber->email }}</td>
                        <td class="px-4 py-3">{{ $subscriber->isConfirmed() ? 'Confirmed' : 'Pending' }}</td>
                        <td class="hidden px-4 py-3 text-muted sm:table-cell">{{ $subscriber->created_at->format('j M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="delete({{ $subscriber->id }})" wire:confirm="Remove this subscriber?" class="text-red-700 hover:underline">Remove</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-muted">No subscribers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $subscribers->links() }}</div>
</div>
