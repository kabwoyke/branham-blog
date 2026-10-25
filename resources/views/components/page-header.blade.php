@props(['eyebrow' => null, 'title', 'intro' => null])

<header class="border-b border-muted/15 bg-gradient-to-b from-brand/[0.07] to-transparent">
    <div class="mx-auto max-w-6xl animate-fade-up px-4 pb-10 pt-14 sm:px-6 sm:pb-12 sm:pt-16">
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-3 text-3xl font-extrabold leading-tight min-[400px]:text-4xl sm:text-5xl">{{ $title }}</h1>
        @if ($intro)
            <p class="mt-4 max-w-2xl text-lg text-muted">{{ $intro }}</p>
        @endif
        {{ $slot }}
    </div>
</header>
