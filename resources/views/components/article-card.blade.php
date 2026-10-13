@props(['article', 'large' => false])

<article {{ $attributes->class(['group flex flex-col']) }}>
    @if ($article->featured_image_url)
        <a href="{{ route('articles.show', $article->slug) }}" tabindex="-1" aria-hidden="true">
            <img
                src="{{ $article->featured_image_url }}"
                alt=""
                width="800"
                height="450"
                loading="lazy"
                class="aspect-video w-full rounded-xl object-cover"
            >
        </a>
    @endif

    <div @class(['flex flex-1 flex-col', 'mt-4' => $article->featured_image_url])>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand">
            <a href="{{ route('categories.show', $article->category->slug) }}" class="hover:underline">{{ $article->category->name }}</a>
            <span class="text-muted">· {{ $article->type->label() }}</span>
        </p>

        <h3 @class(['mt-2 font-bold leading-snug', 'text-2xl' => $large, 'text-xl' => ! $large])>
            <a href="{{ route('articles.show', $article->slug) }}" class="hover:text-brand">{{ $article->title }}</a>
        </h3>

        <p class="mt-2 text-muted">{{ $article->excerpt }}</p>

        <p class="mt-3 text-sm text-muted">
            @if ($article->published_at)
                <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time> ·
            @endif
            {{ $article->reading_time }} min read
        </p>
    </div>
</article>
