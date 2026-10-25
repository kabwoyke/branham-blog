<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold sm:text-3xl">Articles</h1>
        <a href="{{ route('admin.articles.create') }}" class="rounded-lg bg-brand px-4 py-2 shadow-sm text-sm font-medium text-white hover:bg-brand/90">New article</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
        <x-admin.input label="Search titles" name="search" type="search" wire:model.live.debounce.300ms="search" />
        <x-admin.select label="Status" name="status" wire:model.live="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $statusOption)
                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.select label="Category" name="category" wire:model.live="category">
            <option value="">All categories</option>
            @foreach ($categories as $categoryOption)
                <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
            @endforeach
        </x-admin.select>
        <label class="flex items-end gap-2 pb-2 text-sm">
            <input type="checkbox" wire:model.live="trashed" class="size-4 accent-brand">
            Show trash
        </label>
    </div>

    <div class="mt-6 overflow-x-auto card">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-muted/20 text-xs uppercase tracking-wide text-muted">
                <tr>
                    <th scope="col" class="px-4 py-3">Title</th>
                    <th scope="col" class="hidden px-4 py-3 md:table-cell">Category</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="hidden px-4 py-3 sm:table-cell">Date</th>
                    <th scope="col" class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-muted/20">
                @forelse ($articles as $article)
                    <tr wire:key="article-{{ $article->id }}">
                        <td class="px-4 py-3 font-medium">
                            @if ($article->trashed())
                                {{ $article->title }}
                            @else
                                <a href="{{ route('admin.articles.edit', $article) }}" class="hover:text-brand">{{ $article->title }}</a>
                            @endif
                            @if ($article->is_featured)
                                <span class="ml-1 text-xs text-brand">★ Featured</span>
                            @endif
                        </td>
                        <td class="hidden px-4 py-3 text-muted md:table-cell">{{ $article->category->name }}</td>
                        <td class="px-4 py-3">{{ $article->status->label() }}</td>
                        <td class="hidden px-4 py-3 text-muted sm:table-cell">{{ $article->published_at?->format('j M Y, H:i') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            @if ($article->trashed())
                                <button type="button" wire:click="restore({{ $article->id }})" class="text-brand hover:underline">Restore</button>
                            @else
                                @if ($article->status === \App\Enums\ArticleStatus::Published)
                                    @if ($article->newsletter_sent_at)
                                        <span class="mr-3 text-xs text-muted" title="{{ $article->newsletter_sent_at->format('j M Y, H:i') }}">Emailed</span>
                                    @else
                                        <button type="button" wire:click="sendNewsletter({{ $article->id }})" wire:confirm="Email this article to all confirmed subscribers? This cannot be repeated." class="mr-3 text-brand hover:underline">Email subscribers</button>
                                    @endif
                                @endif
                                <a href="{{ route('admin.articles.edit', $article) }}" class="text-brand hover:underline">Edit</a>
                                <button type="button" wire:click="delete({{ $article->id }})" wire:confirm="Move this article to the trash?" class="ml-3 text-red-700 hover:underline">Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-muted">No articles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
</div>
