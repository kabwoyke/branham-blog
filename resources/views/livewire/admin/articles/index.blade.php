<div>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-semibold">Articles</h1>
        <a href="{{ route('admin.articles.create') }}" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand/90">New article</a>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-4">
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

    <div class="mt-6 overflow-x-auto border border-muted/20 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-muted/20 text-xs uppercase tracking-wide text-muted">
                <tr>
                    <th scope="col" class="px-4 py-3">Title</th>
                    <th scope="col" class="px-4 py-3">Category</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3">Date</th>
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
                        <td class="px-4 py-3 text-muted">{{ $article->category->name }}</td>
                        <td class="px-4 py-3">{{ $article->status->label() }}</td>
                        <td class="px-4 py-3 text-muted">{{ $article->published_at?->format('j M Y, H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($article->trashed())
                                <button type="button" wire:click="restore({{ $article->id }})" class="text-brand hover:underline">Restore</button>
                            @else
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
