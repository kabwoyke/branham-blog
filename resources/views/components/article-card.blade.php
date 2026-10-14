@props(['article', 'large' => false, 'horizontal' => false])

<article {{ $attributes->class(['group relative flex', 'flex-col' => ! $horizontal, 'flex-col gap-6 md:flex-row md:items-center md:gap-10' => $horizontal]) }}>
    <div @class(['overflow-hidden rounded-2xl bg-brand/10', 'md:w-3/5 md:shrink-0' => $horizontal])>
        @if ($article->featured_image_url)
            <img
                src="{{ $article->featured_image_url }}"
                alt=""
                width="800"
                height="450"
                loading="lazy"
                decoding="async"
                class="aspect-video w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            >
        @else
            <div class="flex aspect-video w-full items-center justify-center bg-gradient-to-br from-brand/20 to-brand/5" aria-hidden="true">
                <span class="text-5xl font-extrabold text-brand/30">{{ mb_substr($article->category->name, 0, 1) }}</span>
            </div>
        @endif
    </div>

    <div @class(['flex flex-1 flex-col', 'mt-5' => ! $horizontal])>
        <p class="eyebrow">
            {{ $article->category->name }}
            <span class="font-medium normal-case tracking-normal text-muted">· {{ $article->type->label() }}</span>
        </p>

        <h3 @class(['mt-2 font-bold leading-snug tracking-tight', 'text-2xl sm:text-3xl' => $large || $horizontal, 'text-xl' => ! $large && ! $horizontal])>
            <a href="{{ route('articles.show', $article->slug) }}" class="after:absolute after:inset-0 group-hover:text-brand">{{ $article->title }}</a>
        </h3>

        <p @class(['mt-3 text-muted', 'line-clamp-3' => ! $horizontal, 'text-lg' => $horizontal])>{{ $article->excerpt }}</p>

        <p class="mt-4 text-sm text-muted">
            @if ($article->published_at)
                <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('j F Y') }}</time>
                <span aria-hidden="true">·</span>
            @endif
            {{ $article->reading_time }} min read
        </p>
    </div>
</article>
