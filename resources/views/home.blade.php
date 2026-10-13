<x-layouts.app :description="$tagline.'. Observations, perspectives and predictions on society, business and the future.'">
    <section class="mx-auto max-w-6xl px-4 pb-6 pt-16 sm:px-6 sm:pt-24">
        <p class="text-sm font-semibold uppercase tracking-widest text-brand">{{ $tagline }}</p>
        <h1 class="mt-4 max-w-3xl text-4xl font-extrabold leading-tight sm:text-6xl">Observations on society, business and the future.</h1>
        <p class="mt-6 max-w-2xl text-lg text-muted">What is noticed in everyday life, what it could mean for society and for business, and where emerging patterns might lead.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('articles.index') }}" class="btn btn-primary">Read the articles</a>
            <a href="{{ route('about') }}" class="btn btn-outline">About Branham</a>
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="mx-auto mt-16 max-w-6xl px-4 sm:px-6" aria-labelledby="featured-heading">
            <div class="flex items-end justify-between">
                <h2 id="featured-heading" class="text-2xl font-bold">Featured</h2>
                <a href="{{ route('articles.featured') }}" class="text-sm font-semibold text-brand hover:underline">All featured &rarr;</a>
            </div>
            <div class="mt-6 grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $article)
                    <x-article-card :article="$article" :large="$featured->count() < 3" />
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto mt-16 max-w-6xl px-4 sm:px-6" aria-labelledby="latest-heading">
        <div class="flex items-end justify-between">
            <h2 id="latest-heading" class="text-2xl font-bold">Latest</h2>
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-brand hover:underline">All articles &rarr;</a>
        </div>

        @if ($latest->isEmpty())
            <p class="mt-6 text-muted">New articles are on the way.</p>
        @else
            <div class="mt-6 grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto mt-16 max-w-6xl px-4 sm:px-6" aria-labelledby="topics-heading">
        <h2 id="topics-heading" class="text-2xl font-bold">Topics</h2>
        <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <li>
                    <a href="{{ route('categories.show', $category->slug) }}" class="block h-full rounded-xl border border-muted/20 bg-white p-5 transition hover:border-brand">
                        <h3 class="font-bold">{{ $category->name }}</h3>
                        <p class="mt-1 text-sm text-muted">{{ $category->description }}</p>
                        <p class="mt-3 text-xs font-semibold text-brand">{{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.app>
