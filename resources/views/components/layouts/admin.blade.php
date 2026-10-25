@props(['title' => null])

@php
    $badges = auth()->check()
        ? [
            'admin.messages.index' => \App\Models\ContactMessage::unread()->count(),
            'admin.comments.index' => \App\Models\Comment::pending()->count(),
        ]
        : [];

    $links = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
        ['label' => 'Articles', 'route' => 'admin.articles.index', 'icon' => 'M7 3h8l4 4v14H7zM15 3v4h4M10 12h6M10 16h6'],
        ['label' => 'Categories', 'route' => 'admin.categories.index', 'icon' => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
        ['label' => 'Subscribers', 'route' => 'admin.subscribers.index', 'icon' => 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21a8 8 0 0116 0'],
        ['label' => 'Messages', 'route' => 'admin.messages.index', 'icon' => 'M3 6h18v12H3zM3 7l9 7 9-7'],
        ['label' => 'Comments', 'route' => 'admin.comments.index', 'icon' => 'M4 5h16v11H9l-5 4z'],
        ['label' => 'Settings', 'route' => 'admin.settings', 'icon' => 'M12 15a3 3 0 100-6 3 3 0 000 6zM19 12a7 7 0 00-.1-1.2l2-1.5-2-3.4-2.4 1a7 7 0 00-2-1.2L14 3h-4l-.5 2.7a7 7 0 00-2 1.2l-2.4-1-2 3.4 2 1.5A7 7 0 005 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.4 2.4-1a7 7 0 002 1.2L10 21h4l.5-2.7a7 7 0 002-1.2l2.4 1 2-3.4-2-1.5c.1-.4.1-.8.1-1.2z'],
    ];

    $icon = fn (string $path) => '<svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$path.'"/></svg>';
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

        @auth
            <div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="lg:flex">
                {{-- Mobile top bar --}}
                <header class="sticky top-0 z-30 flex items-center justify-between bg-brand px-4 py-3 text-white shadow-md lg:hidden">
                    <a href="{{ route('admin.dashboard') }}" class="text-lg font-extrabold tracking-tight">{{ config('app.name') }}</a>
                    <button type="button" x-on:click="open = true" class="rounded-lg p-2 text-white hover:bg-white/10" aria-label="Open menu" x-bind:aria-expanded="open.toString()" aria-controls="admin-sidebar">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                </header>

                <div x-show="open" x-cloak x-transition.opacity x-on:click="open = false" class="fixed inset-0 z-40 bg-ink/40 lg:hidden" aria-hidden="true"></div>

                <aside id="admin-sidebar"
                    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-linear-to-b from-brand to-[#1d3d34] text-white shadow-xl transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:shrink-0"
                    x-bind:class="{ 'translate-x-0': open }">
                    <div class="flex items-center justify-between px-5 py-5">
                        <a href="{{ route('admin.dashboard') }}" class="leading-tight">
                            <span class="block text-lg font-extrabold tracking-tight">{{ config('app.name') }}</span>
                            <span class="text-[0.65rem] font-semibold uppercase tracking-wider text-white/70">Admin panel</span>
                        </a>
                        <button type="button" x-on:click="open = false" class="rounded-lg p-1.5 text-white/80 hover:bg-white/10 lg:hidden" aria-label="Close menu">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    </div>

                    <nav aria-label="Admin" class="flex-1 space-y-1 overflow-y-auto px-3 text-sm font-semibold">
                        @foreach ($links as $link)
                            @php($active = request()->routeIs($link['route'], str_replace('.index', '.*', $link['route'])))
                            @php($badge = $badges[$link['route']] ?? 0)
                            <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors', 'bg-white text-brand shadow-md' => $active, 'text-white/85 hover:bg-white/10 hover:text-white' => ! $active])>
                                {!! $icon($link['icon']) !!}
                                <span class="flex-1">{{ $link['label'] }}</span>
                                @if ($badge > 0)
                                    <span @class(['rounded-full px-2 py-0.5 text-xs font-bold', 'bg-brand text-white' => $active, 'bg-amber-400 text-ink' => ! $active])><span class="sr-only">{{ $badge }} new</span><span aria-hidden="true">{{ $badge }}</span></span>
                                @endif
                            </a>
                        @endforeach
                    </nav>

                    <div class="space-y-1 border-t border-white/15 px-3 py-4 text-sm font-semibold">
                        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-white/75 hover:bg-white/10 hover:text-white">
                            {!! $icon('M14 4h6v6M20 4l-9 9M18 14v5H5V6h5') !!}
                            View site
                        </a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-white/75 hover:bg-white/10 hover:text-white">
                                {!! $icon('M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10') !!}
                                Sign out
                            </button>
                        </form>
                    </div>
                </aside>

                <div class="min-w-0 flex-1 bg-linear-to-br from-brand/5 via-paper to-amber-50">
                    <x-flash />

                    <main id="main" class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:px-10">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        @else
            <x-flash />

            <main id="main" class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10">
                {{ $slot }}
            </main>
        @endauth

        @livewireScripts
    </body>
</html>
