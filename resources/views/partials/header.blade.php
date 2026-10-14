@php
    $links = [
        ['label' => 'Home', 'route' => 'home', 'match' => ['home']],
        ['label' => 'Articles', 'route' => 'articles.index', 'match' => ['articles.index', 'articles.show', 'categories.*']],
        ['label' => 'Featured', 'route' => 'articles.featured', 'match' => ['articles.featured']],
        ['label' => 'About', 'route' => 'about', 'match' => ['about']],
        ['label' => 'Contact', 'route' => 'contact', 'match' => ['contact']],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-muted/15 bg-paper/90 backdrop-blur" x-data="{ open: false }">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-extrabold tracking-tight text-ink">
            <span class="inline-block size-2.5 rounded-full bg-brand" aria-hidden="true"></span>
            {{ config('app.name') }}
        </a>

        <button type="button" class="rounded-lg p-2 text-ink hover:bg-brand/10 lg:hidden" x-on:click="open = ! open" x-bind:aria-expanded="open" aria-controls="mobile-nav" aria-label="Toggle navigation">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" /></svg>
        </button>

        <nav aria-label="Primary" class="hidden items-center gap-1 text-sm font-semibold lg:flex">
            @foreach ($links as $link)
                @php($active = request()->routeIs(...$link['match']))
                <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['rounded-full px-4 py-2 transition-colors', 'bg-brand/10 text-brand' => $active, 'text-ink hover:bg-brand/5 hover:text-brand' => ! $active])>{{ $link['label'] }}</a>
            @endforeach
            <form action="{{ route('articles.index') }}#results" method="GET" role="search" class="ml-3">
                <label for="header-search" class="sr-only">Search articles</label>
                <input id="header-search" type="search" name="q" placeholder="Search" maxlength="100" class="w-36 rounded-full border border-muted/30 bg-white px-4 py-2 text-sm font-normal transition-all focus:w-52">
            </form>
        </nav>
    </div>

    <nav id="mobile-nav" x-cloak x-show="open" aria-label="Mobile" class="border-t border-muted/15 bg-paper px-4 py-3 text-sm font-semibold lg:hidden">
        @foreach ($links as $link)
            @php($active = request()->routeIs(...$link['match']))
            <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['block rounded-lg px-3 py-2.5', 'bg-brand/10 text-brand' => $active, 'text-ink hover:text-brand' => ! $active])>{{ $link['label'] }}</a>
        @endforeach
        <form action="{{ route('articles.index') }}#results" method="GET" role="search" class="px-3 py-2">
            <label for="mobile-search" class="sr-only">Search articles</label>
            <input id="mobile-search" type="search" name="q" placeholder="Search articles" maxlength="100" class="w-full rounded-lg border border-muted/30 bg-white px-4 py-2 text-sm font-normal">
        </form>
    </nav>
</header>
