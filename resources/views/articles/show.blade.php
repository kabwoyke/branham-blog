@php
    $url = route('articles.show', $article->slug);
    $metaDescription = $article->meta_description ?: $article->excerpt;
@endphp

<x-layouts.app :title="$article->meta_title ?: $article->title" :description="$metaDescription">
    @if ($isPreview)
        <div class="bg-amber-100 px-4 py-2 text-center text-sm font-semibold text-amber-900" role="status">
            Preview: this article is {{ $article->status->value }} and is not visible to the public.
        </div>
    @endif

    <article class="mx-auto max-w-3xl px-4 pt-14 sm:px-6">
        <header>
            <p class="text-sm font-semibold uppercase tracking-wide text-brand">
                <a href="{{ route('categories.show', $article->category->slug) }}" class="hover:underline">{{ $article->category->name }}</a>
                <span class="text-muted">· {{ $article->type->label() }}</span>
            </p>
            <h1 class="mt-3 text-4xl font-extrabold leading-tight sm:text-5xl">{{ $article->title }}</h1>
            <p class="mt-4 text-lg text-muted">{{ $article->excerpt }}</p>

            <p class="mt-6 text-sm text-muted">
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
                    class="aspect-video w-full rounded-xl object-cover"
                >
            </figure>
        @endif

        <div class="prose-article mt-10">
            {!! $article->body_html !!}
        </div>

        <div
            class="mt-12 flex flex-wrap items-center gap-3 border-t border-muted/20 pt-6"
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
            <span class="text-sm font-semibold">Share</span>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($url) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline !px-4 !py-2">LinkedIn</a>
            <a href="mailto:?subject={{ rawurlencode($article->title) }}&body={{ rawurlencode($url) }}" class="btn btn-outline !px-4 !py-2">Email</a>
            <button type="button" x-on:click="copy" class="btn btn-outline !px-4 !py-2">Copy link</button>
            <span class="text-sm font-semibold text-brand" role="status" aria-live="polite" x-cloak x-show="copied">Copied</span>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto mt-20 max-w-6xl px-4 sm:px-6" aria-labelledby="related-heading">
            <h2 id="related-heading" class="text-2xl font-bold">Related articles</h2>
            <div class="mt-6 grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <x-article-card :article="$item" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
