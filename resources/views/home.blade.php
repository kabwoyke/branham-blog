@php
    $siteDescription = $tagline.'. Observations, perspectives and predictions on society, business and the future.';
    $schema = [[
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('app.name'),
        'url' => route('home'),
        'description' => $siteDescription,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => route('articles.index').'?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ]];
    $lead = $featured->first();
    $otherFeatured = $featured->slice(1);
@endphp

<x-layouts.app :description="$siteDescription" :schema="$schema">
    <section class="relative overflow-hidden border-b border-muted/15 bg-gradient-to-b from-brand/[0.09] to-transparent">
        <div class="mx-auto max-w-6xl px-4 pb-16 pt-16 sm:px-6 sm:pb-24 sm:pt-24">
            <p class="eyebrow animate-fade-up">{{ $tagline }}</p>
            <h1 class="mt-5 max-w-3xl animate-fade-up [animation-delay:100ms] text-3xl font-extrabold leading-[1.15] min-[400px]:text-4xl sm:text-6xl sm:leading-[1.1]">Observations on society, business and the future.</h1>
            <p class="mt-6 max-w-2xl animate-fade-up [animation-delay:200ms] text-lg text-muted sm:text-xl">What is noticed in everyday life, what it could mean for society and for business, and where emerging patterns might lead.</p>
            <div class="mt-9 flex flex-wrap gap-3 animate-fade-up [animation-delay:300ms]">
                <a href="{{ route('articles.index') }}" class="btn btn-primary">Read the articles</a>
                <a href="{{ route('about') }}" class="btn btn-outline">About Branham</a>
            </div>
        </div>
    </section>

    @if ($lead)
        <section data-reveal class="mx-auto mt-16 max-w-6xl px-4 sm:px-6" aria-labelledby="featured-heading">
            <div class="flex items-end justify-between">
                <h2 id="featured-heading" class="section-title">Featured</h2>
                <a href="{{ route('articles.featured') }}" class="text-sm font-semibold text-brand hover:underline">All featured &rarr;</a>
            </div>
            <div class="mt-8">
                <x-article-card :article="$lead" :horizontal="true" />
            </div>
            @if ($otherFeatured->isNotEmpty())
                <div class="mt-12 grid gap-10 border-t border-muted/15 pt-12 md:grid-cols-2">
                    @foreach ($otherFeatured as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <section data-reveal class="mx-auto mt-20 max-w-6xl px-4 sm:px-6" aria-labelledby="latest-heading">
        <div class="flex items-end justify-between">
            <h2 id="latest-heading" class="section-title">Latest</h2>
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-brand hover:underline">All articles &rarr;</a>
        </div>

        @if ($latest->isEmpty())
            <p class="mt-6 text-muted">New articles are on the way.</p>
        @else
            <div class="mt-8 grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latest as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        @endif
    </section>

    <section data-reveal class="mx-auto mt-20 max-w-6xl px-4 sm:px-6" aria-labelledby="topics-heading">
        <h2 id="topics-heading" class="section-title">Topics</h2>
        <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <li data-reveal>
                    <a href="{{ route('categories.show', $category->slug) }}" class="group flex h-full flex-col card p-6 transition hover:-translate-y-0.5 hover:border-brand hover:shadow-md">
                        <h3 class="font-bold group-hover:text-brand">{{ $category->name }}</h3>
                        <p class="mt-2 flex-1 text-sm text-muted">{{ $category->description }}</p>
                        <p class="mt-4 text-xs font-semibold text-brand">{{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }} &rarr;</p>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section data-reveal class="mx-auto mt-20 max-w-6xl px-4 sm:px-6" aria-labelledby="newsletter-heading">
        <div class="rounded-3xl bg-brand px-6 py-10 text-white sm:px-12 sm:py-14">
            <h2 id="newsletter-heading" class="text-2xl font-bold sm:text-3xl">Get new articles by email</h2>
            <p class="mt-3 max-w-xl text-white/85">Occasional observations on society, business and the future. Unsubscribe at any time.</p>
            <div class="mt-7 max-w-xl rounded-xl bg-paper p-4 text-ink">
                <livewire:newsletter.signup location="home" />
            </div>
        </div>
    </section>
</x-layouts.app>
