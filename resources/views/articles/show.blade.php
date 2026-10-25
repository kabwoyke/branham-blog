@php
    $url = route('articles.show', $article->slug);
    $metaDescription = $article->meta_description ?: $article->excerpt;
    $imageUrl = $article->featured_image_url ? url($article->featured_image_url) : null;
    $schema = [
        array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $metaDescription,
            'image' => $imageUrl,
            'datePublished' => $article->published_at?->toAtomString(),
            'dateModified' => $article->updated_at?->toAtomString(),
            'articleSection' => $article->category->name,
            'mainEntityOfPage' => $url,
            'author' => ['@type' => 'Person', 'name' => $article->author->name],
            'publisher' => ['@type' => 'Person', 'name' => config('app.name')],
        ]),
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $article->category->name, 'item' => route('categories.show', $article->category->slug)],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $article->title, 'item' => $url],
            ],
        ],
    ];
@endphp

<x-layouts.app
    :title="$article->meta_title ?: $article->title"
    :description="$metaDescription"
    :image="$imageUrl"
    og-type="article"
    :published-at="$article->published_at?->toAtomString()"
    :schema="$schema"
    :noindex="$isPreview"
>
    @if ($isPreview)
        <div class="bg-amber-100 px-4 py-2 text-center text-sm font-semibold text-amber-900" role="status">
            Preview: this article is {{ $article->status->value }} and is not visible to the public.
        </div>
    @endif

    <div
        class="fixed inset-x-0 top-0 z-50 h-1 bg-transparent"
        aria-hidden="true"
        x-data="{ progress: 0 }"
        x-on:scroll.window.passive="
            const rect = document.getElementById('article-body').getBoundingClientRect()
            const span = rect.height - window.innerHeight
            progress = span > 0 ? Math.min(100, Math.max(0, -rect.top / span * 100)) : 100
        "
    >
        <div class="h-full bg-brand transition-[width] duration-100" x-bind:style="`width: ${progress}%`"></div>
    </div>

    <article class="mx-auto max-w-3xl px-4 pt-14 sm:px-6">
        <header class="animate-fade-up">
            <nav aria-label="Breadcrumb" class="text-sm text-muted">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('articles.index') }}" class="hover:text-brand">Articles</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('categories.show', $article->category->slug) }}" class="hover:text-brand">{{ $article->category->name }}</a></li>
                </ol>
            </nav>
            <p class="eyebrow mt-6">{{ $article->type->label() }}</p>
            <h1 class="mt-3 text-3xl font-extrabold leading-[1.15] sm:text-5xl sm:leading-[1.1]">{{ $article->title }}</h1>
            <p class="mt-5 text-lg leading-relaxed text-muted sm:text-xl">{{ $article->excerpt }}</p>

            <p class="mt-6 border-t border-muted/15 pt-5 text-sm text-muted">
                By <span class="font-semibold text-ink">{{ $article->author->name }}</span>
                @if ($article->published_at)
                    · <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
                @endif
                · {{ $article->reading_time }} min read
            </p>
        </header>

        @if ($article->featured_image_url)
            <figure class="mt-8">
                <img
                    src="{{ $article->featured_image_url }}"
                    alt="{{ $article->featured_image_alt }}"
                    width="1200"
                    height="675"
                    fetchpriority="high"
                    decoding="async"
                    class="aspect-video w-full rounded-2xl object-cover"
                >
            </figure>
        @endif

        @if (count($headings) >= 3)
            <nav aria-labelledby="toc-heading" class="mt-10 rounded-2xl border border-muted/20 p-5 sm:p-6">
                <h2 id="toc-heading" class="eyebrow">In this article</h2>
                <ol class="mt-3 space-y-1.5 text-sm">
                    @foreach ($headings as $heading)
                        <li class="{{ $heading['level'] === 3 ? 'pl-4' : '' }}">
                            <a href="#{{ $heading['id'] }}" class="text-ink hover:text-brand">{{ $heading['text'] }}</a>
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <div id="article-body" class="prose-article mt-10 [&_h2]:scroll-mt-24 [&_h3]:scroll-mt-24">
            {!! $article->body_html !!}
        </div>

        <div
            class="mt-14 flex flex-wrap items-center gap-3 border-t border-muted/20 pt-6"
            x-data="{
                copied: false,
                copy() {
                    navigator.clipboard.writeText(@js($url)).then(() => {
                        this.copied = true
                        setTimeout(() => this.copied = false, 2000)
                    })
                },
            }"
        >
            <span class="text-sm font-semibold">Share this article</span>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline !px-4 !py-2">LinkedIn</a>
            <a href="mailto:?subject={{ rawurlencode($article->title) }}&body={{ rawurlencode($url) }}" class="btn btn-outline !px-4 !py-2">Email</a>
            <button type="button" x-on:click="copy" class="btn btn-outline !px-4 !py-2">Copy link</button>
            <span class="text-sm font-semibold text-brand" role="status" aria-live="polite" x-cloak x-show="copied">Copied</span>
        </div>

        @if ($previous || $next)
            <nav aria-label="More articles" class="mt-10 grid gap-4 sm:grid-cols-2">
                @if ($previous)
                    <a href="{{ route('articles.show', $previous->slug) }}" rel="prev" class="rounded-2xl border border-muted/20 p-5 hover:border-brand">
                        <span class="eyebrow">Previous</span>
                        <span class="mt-2 block font-bold leading-snug">{{ $previous->title }}</span>
                    </a>
                @endif
                @if ($next)
                    <a href="{{ route('articles.show', $next->slug) }}" rel="next" class="rounded-2xl border border-muted/20 p-5 hover:border-brand sm:col-start-2 sm:text-right">
                        <span class="eyebrow">Next</span>
                        <span class="mt-2 block font-bold leading-snug">{{ $next->title }}</span>
                    </a>
                @endif
            </nav>
        @endif

        <aside data-reveal class="mt-10 rounded-2xl bg-brand/10 p-6 sm:p-8" aria-labelledby="article-newsletter-heading">
            <h2 id="article-newsletter-heading" class="text-lg font-bold">Enjoyed this? Get new articles by email.</h2>
            <div class="mt-4">
                <livewire:newsletter.signup location="article" />
            </div>
        </aside>
    </article>

    @if ($related->isNotEmpty())
        <section data-reveal class="mx-auto mt-24 max-w-6xl border-t border-muted/15 px-4 pt-14 sm:px-6" aria-labelledby="related-heading">
            <h2 id="related-heading" class="section-title">Related articles</h2>
            <div class="mt-8 grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-article-card :article="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
