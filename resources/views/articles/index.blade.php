@php
    $heading = $category?->name ?? 'Articles';
    $intro = $category?->description ?? 'Observations, perspectives and predictions on society, business and the future.';
@endphp

<x-layouts.app :title="$heading" :description="$intro">
    <section class="mx-auto max-w-6xl px-4 pt-14 sm:px-6">
        <h1 class="text-4xl font-extrabold sm:text-5xl">{{ $heading }}</h1>
        <p class="mt-3 max-w-2xl text-lg text-muted">{{ $intro }}</p>

        <nav aria-label="Categories" class="mt-8 flex flex-wrap gap-2 text-sm font-semibold">
            <a href="{{ route('articles.index') }}" @if (! $category) aria-current="page" @endif @class(['rounded-full border px-4 py-2', 'border-brand bg-brand text-white' => ! $category, 'border-muted/30 hover:border-brand hover:text-brand' => $category])>All</a>
            @foreach ($categories as $item)
                @php($isCurrent = $category?->is($item))
                <a href="{{ route('categories.show', $item->slug) }}" @if ($isCurrent) aria-current="page" @endif @class(['rounded-full border px-4 py-2', 'border-brand bg-brand text-white' => $isCurrent, 'border-muted/30 hover:border-brand hover:text-brand' => ! $isCurrent])>{{ $item->name }}</a>
            @endforeach
        </nav>
    </section>

    <section class="mx-auto mt-10 max-w-6xl px-4 sm:px-6" aria-label="Article list">
        @if ($articles->isEmpty())
            <p class="rounded-xl border border-muted/20 bg-white p-8 text-center text-muted">No articles found.</p>
        @else
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>

            <div class="mt-12">{{ $articles->links() }}</div>
        @endif
    </section>
</x-layouts.app>
