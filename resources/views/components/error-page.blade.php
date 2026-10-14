@props(['code', 'title', 'message'])

<x-layouts.app :title="$title" :noindex="true">
    <section class="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-widest text-brand">{{ $code }}</p>
        <h1 class="mt-3 text-4xl font-extrabold sm:text-5xl">{{ $title }}</h1>
        <p class="mt-4 text-lg text-muted">{{ $message }}</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="btn btn-primary">Back to home</a>
            <a href="{{ route('articles.index') }}" class="btn btn-outline">Browse articles</a>
        </div>
    </section>
</x-layouts.app>
