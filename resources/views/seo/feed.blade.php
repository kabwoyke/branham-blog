<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <description>{{ $tagline }}</description>
        <language>en</language>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
@foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('articles.show', $article->slug) }}</link>
            <guid isPermaLink="true">{{ route('articles.show', $article->slug) }}</guid>
            <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
            <category>{{ $article->category->name }}</category>
            <description>{{ $article->excerpt }}</description>
        </item>
@endforeach
    </channel>
</rss>
