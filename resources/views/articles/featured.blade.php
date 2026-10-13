<x-layouts.app title="Featured articles" description="Selected articles worth reading first.">
    <section class="mx-auto max-w-6xl px-4 pt-14 sm:px-6">
        <h1 class="text-4xl font-extrabold sm:text-5xl">Featured articles</h1>
        <p class="mt-3 max-w-2xl text-lg text-muted">Selected pieces worth reading first.</p>
    </section>

    <section class="mx-auto mt-10 max-w-6xl px-4 sm:px-6" aria-label="Featured article list">
        @if ($articles->isEmpty())
            <div class="rounded-xl border border-muted/20 bg-white p-8 text-center">
                <p class="text-lg font-semibold">No featured articles yet</p>
                <a href="{{ route('articles.index') }}" class="btn btn-outline mt-5 !py-2">Browse all articles</a>
            </div>
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
