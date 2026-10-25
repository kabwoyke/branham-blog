<x-mail::message>
# {{ $article->title }}

{{ $article->category->name }} · {{ $article->reading_time }} min read

{{ $article->excerpt }}

<x-mail::button :url="$articleUrl">
Read the article
</x-mail::button>

You are receiving this because you subscribed to {{ config('app.name') }}. [Unsubscribe]({{ $unsubscribeUrl }}).
</x-mail::message>
