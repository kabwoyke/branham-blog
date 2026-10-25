@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ ($title ?? 'Sign in').' | '.config('app.name') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen">
        <main id="main" class="grid min-h-screen lg:grid-cols-5">
            <aside class="relative hidden flex-col justify-between overflow-hidden bg-brand p-12 text-white lg:col-span-2 lg:flex" aria-hidden="true">
                <div class="pointer-events-none absolute -bottom-32 -left-24 size-96 rounded-full bg-white/5"></div>
                <div class="pointer-events-none absolute -right-20 -top-20 size-72 rounded-full bg-white/5"></div>

                <a href="{{ route('home') }}" tabindex="-1" class="relative flex items-center gap-2 text-xl font-extrabold tracking-tight">
                    {{ config('app.name') }}
                </a>

                <p class="relative max-w-sm text-3xl font-semibold leading-snug tracking-tight">
                    Observations, perspectives and predictions on society, business and the future.
                </p>

                <p class="relative text-sm text-white/70">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            </aside>

            <div class="flex items-center justify-center px-6 py-12 lg:col-span-3">
                <div class="w-full max-w-sm">
                    <a href="{{ route('home') }}" class="mb-10 flex items-center gap-2 text-xl font-extrabold tracking-tight lg:hidden">
                        {{ config('app.name') }}
                    </a>

                    {{ $slot }}

                    <p class="mt-10 text-center text-sm">
                        <a href="{{ route('home') }}" class="text-muted hover:text-brand">&larr; Back to the site</a>
                    </p>
                </div>
            </div>
        </main>

        @livewireScripts
    </body>
</html>
