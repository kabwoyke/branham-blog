@props(['title' => null])

@php
    $links = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
        ['label' => 'Articles', 'route' => 'admin.articles.index'],
        ['label' => 'Categories', 'route' => 'admin.categories.index'],
        ['label' => 'Subscribers', 'route' => 'admin.subscribers.index'],
        ['label' => 'Messages', 'route' => 'admin.messages.index'],
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

        <header class="border-b border-muted/20 bg-white">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-4 sm:px-6">
                <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl font-semibold">{{ config('app.name') }} <span class="font-sans text-xs font-medium uppercase tracking-wide text-muted">Admin</span></a>

                @auth
                    <nav aria-label="Admin" class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium">
                        @foreach ($links as $link)
                            <a href="{{ route($link['route']) }}" @class(['hover:text-brand', 'text-brand underline underline-offset-4' => request()->routeIs($link['route'], str_replace('.index', '.*', $link['route']))])>{{ $link['label'] }}</a>
                        @endforeach
                        <a href="{{ route('home') }}" class="text-muted hover:text-brand" target="_blank" rel="noopener">View site</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="text-muted hover:text-brand">Sign out</button>
                        </form>
                    </nav>
                @endauth
            </div>
        </header>

        <x-flash />

        <main id="main" class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
