<x-layouts.app title="About" :description="$tagline">
    <section class="mx-auto max-w-3xl px-4 pt-14 sm:px-6">
        <p class="text-sm font-semibold uppercase tracking-widest text-brand">{{ $tagline }}</p>
        <h1 class="mt-3 text-4xl font-extrabold sm:text-5xl">About</h1>

        <div class="prose-article mt-8">
            {!! $content !!}
        </div>

        <a href="{{ route('contact') }}" class="btn btn-primary mt-10">Get in touch</a>
    </section>
</x-layouts.app>
