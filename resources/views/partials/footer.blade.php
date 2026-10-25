@php
    $contactEmail = rescue(fn () => \App\Models\Setting::get('contact_email'));
    $linkedin = rescue(fn () => \App\Models\Setting::get('linkedin_url'));
    $tagline = rescue(fn () => \App\Models\Setting::get('tagline'));
@endphp

<footer class="mt-24 bg-ink text-white/75">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.2fr_1fr_1.4fr]">
        <div>
            <p class="flex items-center gap-2 text-lg font-extrabold text-white">
                {{ config('app.name') }}
            </p>
            @if ($tagline)
                <p class="mt-3 max-w-xs text-sm">{{ $tagline }}</p>
            @endif
        </div>

        <nav aria-label="Footer">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white">Explore</p>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="{{ route('articles.index') }}" class="hover:text-white">Articles</a></li>
                <li><a href="{{ route('articles.featured') }}" class="hover:text-white">Featured</a></li>
                <li><a href="{{ route('about') }}" class="hover:text-white">About</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                <li><a href="{{ route('feed') }}" class="hover:text-white">RSS feed</a></li>
                @if ($linkedin)
                    <li><a href="{{ $linkedin }}" class="hover:text-white" rel="noopener" target="_blank">LinkedIn</a></li>
                @endif
            </ul>
        </nav>

        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white">Get new articles by email</p>
            <div class="mt-4 max-w-md rounded-xl bg-paper p-4 text-ink">
                <livewire:newsletter.signup location="footer" />
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm sm:flex-row sm:justify-between sm:px-6">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            @if ($contactEmail)
                <a href="mailto:{{ $contactEmail }}" class="hover:text-white">{{ $contactEmail }}</a>
            @endif
        </div>
    </div>
</footer>
