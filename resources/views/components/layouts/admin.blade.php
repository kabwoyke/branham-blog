@props(['title' => null])

@php
    $links = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
        ['label' => 'Articles', 'route' => 'admin.articles.index'],
        ['label' => 'Categories', 'route' => 'admin.categories.index'],
        ['label' => 'Subscribers', 'route' => 'admin.subscribers.index'],
        ['label' => 'Messages', 'route' => 'admin.messages.index'],
        ['label' => 'Comments', 'route' => 'admin.comments.index'],
        ['label' => 'Settings', 'route' => 'admin.settings'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ ($title ?? 'Admin').' | '.config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-brand focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

        <header class="sticky top-0 z-40 border-b border-muted/15 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:px-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-lg font-extrabold tracking-tight">
                    {{ config('app.name') }}
                    <span class="rounded-full bg-brand/10 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wider text-brand">Admin</span>
                </a>

                @auth
                    <nav aria-label="Admin" class="-mx-4 flex w-[calc(100%+2rem)] items-center gap-1 overflow-x-auto px-4 pb-1 text-sm font-semibold whitespace-nowrap sm:mx-0 sm:w-auto sm:flex-wrap sm:overflow-visible sm:px-0 sm:pb-0">
                        @foreach ($links as $link)
                            @php($active = request()->routeIs($link['route'], str_replace('.index', '.*', $link['route'])))
                            <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['rounded-full px-3 py-1.5 transition-colors', 'bg-brand/10 text-brand' => $active, 'hover:bg-brand/5 hover:text-brand' => ! $active])>{{ $link['label'] }}</a>
                        @endforeach
                        <span class="mx-2 hidden h-5 w-px bg-muted/30 sm:block" aria-hidden="true"></span>
                        <a href="{{ route('home') }}" class="rounded-full px-3 py-1.5 text-muted hover:text-brand" target="_blank" rel="noopener">View site</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full px-3 py-1.5 text-muted hover:text-brand">Sign out</button>
                        </form>
                    </nav>
                @endauth
            </div>
        </header>

        <x-flash />

        <main id="main" class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
