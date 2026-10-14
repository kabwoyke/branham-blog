<x-layouts.app :title="$title">
    <section class="mx-auto max-w-2xl px-4 py-20 sm:px-6">
        <h1 class="text-3xl font-extrabold sm:text-4xl">{{ $title }}</h1>
        <p class="mt-4 text-lg text-muted">{{ $message }}</p>
        <a href="{{ route('articles.index') }}" class="btn btn-primary mt-8">Read the articles</a>
    </section>
</x-layouts.app>
