@php
    $contactEmail = rescue(fn () => \App\Models\Setting::get('contact_email'));
    $linkedin = rescue(fn () => \App\Models\Setting::get('linkedin_url'));
@endphp

<footer class="mt-20 border-t border-muted/20">
    <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-10 text-sm text-muted sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <p class="font-bold text-ink">{{ config('app.name') }}</p>
            <p class="mt-1">&copy; {{ date('Y') }}. All rights reserved.</p>
        </div>

        <ul class="flex flex-wrap gap-x-6 gap-y-2 font-medium">
            <li><a href="{{ route('articles.index') }}" class="hover:text-brand">Articles</a></li>
            <li><a href="{{ route('about') }}" class="hover:text-brand">About</a></li>
            <li><a href="{{ route('contact') }}" class="hover:text-brand">Contact</a></li>
            @if ($contactEmail)
                <li><a href="mailto:{{ $contactEmail }}" class="hover:text-brand">{{ $contactEmail }}</a></li>
            @endif
            @if ($linkedin)
                <li><a href="{{ $linkedin }}" class="hover:text-brand" rel="noopener" target="_blank">LinkedIn</a></li>
            @endif
        </ul>
    </div>
</footer>
