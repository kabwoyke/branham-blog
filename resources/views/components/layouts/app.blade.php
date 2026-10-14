@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'ogType' => 'website',
    'publishedAt' => null,
    'schema' => [],
    'noindex' => false,
])

@php
    $siteName = config('app.name');
    $pageTitle = $title ? $title.' | '.$siteName : $siteName.': Business analyst and social researcher';
    $canonical = url()->current();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
        @if ($noindex)
            <meta name="robots" content="noindex, nofollow">
        @endif
        <link rel="canonical" href="{{ $canonical }}">
        <link rel="alternate" type="application/rss+xml" title="{{ $siteName }}" href="{{ route('feed') }}">

        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:title" content="{{ $title ?: $siteName }}">
        <meta property="og:url" content="{{ $canonical }}">
        @if ($description)
            <meta property="og:description" content="{{ $description }}">
        @endif
        @if ($image)
            <meta property="og:image" content="{{ $image }}">
        @endif
        @if ($publishedAt)
            <meta property="article:published_time" content="{{ $publishedAt }}">
        @endif
        <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $title ?: $siteName }}">
        @if ($description)
            <meta name="twitter:description" content="{{ $description }}">
        @endif
        @if ($image)
            <meta name="twitter:image" content="{{ $image }}">
        @endif

        @foreach ($schema as $graph)
            <script type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
        @endforeach

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="flex min-h-screen flex-col">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-brand focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

        @include('partials.header')

        <x-flash />

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.footer')

        @livewireScripts
    </body>
</html>
