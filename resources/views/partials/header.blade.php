@php
    $links = [
        ['label' => 'Home', 'route' => 'home', 'match' => ['home']],
        ['label' => 'Articles', 'route' => 'articles.index', 'match' => ['articles.*', 'categories.*']],
        ['label' => 'About', 'route' => 'about', 'match' => ['about']],
        ['label' => 'Contact', 'route' => 'contact', 'match' => ['contact']],
    ];
@endphp

<header class="border-b border-muted/20" x-data="{ open: false }">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6">
        <a href="{{ route('home') }}" class="text-2xl font-extrabold tracking-tight text-ink">{{ config('app.name') }}</a>

        <button type="button" class="rounded-lg p-2 text-ink md:hidden" x-on:click="open = ! open" x-bind:aria-expanded="open" aria-controls="mobile-nav" aria-label="Toggle navigation">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" /></svg>
        </button>

        <nav aria-label="Primary" class="hidden items-center gap-8 text-sm font-semibold md:flex">
            @foreach ($links as $link)
                @php($active = request()->routeIs(...$link['match']))
                <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['hover:text-brand', 'text-brand' => $active, 'text-ink' => ! $active])>{{ $link['label'] }}</a>
            @endforeach
        </nav>
    </div>

    <nav id="mobile-nav" x-cloak x-show="open" aria-label="Mobile" class="border-t border-muted/20 px-4 py-3 text-sm font-semibold md:hidden">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="block py-2 text-ink hover:text-brand">{{ $link['label'] }}</a>
        @endforeach
    </nav>
</header>
