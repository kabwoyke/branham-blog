<x-layouts.app title="About" :description="$tagline">
    <x-page-header :eyebrow="$tagline" title="About" />

    <section class="mx-auto max-w-3xl px-4 pt-12 sm:px-6">
        <div class="prose-article">
            {!! $content !!}
        </div>

        <a href="{{ route('contact') }}" class="btn btn-primary mt-10">Get in touch</a>
    </section>
</x-layouts.app>
