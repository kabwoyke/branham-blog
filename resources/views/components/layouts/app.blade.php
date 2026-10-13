@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ? $title.' | '.config('app.name') : config('app.name').': Business analyst and social researcher' }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="flex min-h-screen flex-col">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-brand focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

        @include('partials.header')

        <x-flash />

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.footer')

        @livewireScripts
    </body>
</html>
