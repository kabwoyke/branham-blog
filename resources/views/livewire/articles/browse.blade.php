<div>
    <x-page-header :eyebrow="$category ? 'Topic' : null" :title="$heading" :intro="$intro">
        <form role="search" id="search" wire:submit.prevent class="mt-8 grid gap-3 sm:grid-cols-[1fr_auto]">
            <div>
                <label for="article-search" class="sr-only">Search articles</label>
                <input
                    id="article-search"
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search titles, summaries and full text…"
                    autocomplete="off"
                    maxlength="100"
                    class="block w-full rounded-lg border border-muted/40 bg-white px-4 py-3 text-base"
                >
            </div>
            <div>
                <label for="article-type" class="sr-only">Filter by type</label>
                <select id="article-type" wire:model.live="type" class="block w-full rounded-lg border border-muted/40 bg-white px-4 py-3 text-base sm:w-auto">
                    <option value="">All types</option>
                    @foreach ($types as $typeOption)
                        <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <nav aria-label="Categories" class="mt-6 flex flex-wrap gap-2 text-sm font-semibold">
            @php($carry = array_filter(['q' => trim($search) ?: null, 'type' => $type ?: null]))
            <a href="{{ route('articles.index', $carry) }}" wire:navigate @if (! $category) aria-current="page" @endif @class(['rounded-full border px-4 py-2', 'border-brand bg-brand text-white' => ! $category, 'border-muted/30 bg-white hover:border-brand hover:text-brand' => $category])>All</a>
            @foreach ($categories as $item)
                @php($isCurrent = $category?->is($item))
                <a href="{{ route('categories.show', ['category' => $item->slug] + $carry) }}" wire:navigate @if ($isCurrent) aria-current="page" @endif @class(['rounded-full border px-4 py-2', 'border-brand bg-brand text-white' => $isCurrent, 'border-muted/30 bg-white hover:border-brand hover:text-brand' => ! $isCurrent])>{{ $item->name }}</a>
            @endforeach
        </nav>
    </x-page-header>

    @php($summary = $articles->total().' '.Str::plural('article', $articles->total()).(trim($search) !== '' ? ' matching “'.trim($search).'”' : ''))

    <section id="results" class="mx-auto mt-10 max-w-6xl scroll-mt-6 px-4 sm:px-6" aria-label="Article list">
        <p class="mb-6 text-sm text-muted" role="status" aria-live="polite">
            {{ $summary }}
            <span wire:loading class="ml-2">Updating…</span>
        </p>

        @if ($articles->isEmpty())
            <div class="card p-8 text-center">
                <p class="text-lg font-semibold">No articles found</p>
                <p class="mt-1 text-muted">
                    @if ($isFiltered)
                        Try different words or remove a filter.
                    @else
                        New articles are on the way.
                    @endif
                </p>
                @if ($isFiltered)
                    <button type="button" wire:click="clearFilters" class="btn btn-outline mt-5 !py-2">Clear search and filters</button>
                @endif
            </div>
        @else
            <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" wire:key="article-{{ $article->id }}" />
                @endforeach
            </div>

            <div class="mt-12">{{ $articles->links() }}</div>
        @endif
    </section>
</div>
