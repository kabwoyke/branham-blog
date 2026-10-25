@php
    $stats = [
        ['label' => 'Published', 'value' => $publishedCount, 'route' => route('admin.articles.index', ['status' => 'published']), 'hint' => 'live articles', 'tone' => 'bg-emerald-100 text-emerald-700', 'icon' => 'M5 13l4 4L19 7'],
        ['label' => 'Drafts', 'value' => $draftCount, 'route' => route('admin.articles.index', ['status' => 'draft']), 'hint' => 'in progress', 'tone' => 'bg-amber-100 text-amber-700', 'icon' => 'M4 20h4L19 9l-4-4L4 16zM13 7l4 4'],
        ['label' => 'Scheduled', 'value' => $scheduledCount, 'route' => route('admin.articles.index', ['status' => 'scheduled']), 'hint' => 'queued to publish', 'tone' => 'bg-sky-100 text-sky-700', 'icon' => 'M12 7v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Views', 'value' => number_format($viewsLast30Days), 'route' => null, 'hint' => 'last 30 days', 'tone' => 'bg-violet-100 text-violet-700', 'icon' => 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12zM12 15a3 3 0 100-6 3 3 0 000 6z'],
        ['label' => 'Subscribers', 'value' => $subscriberCount, 'route' => route('admin.subscribers.index'), 'hint' => 'confirmed', 'tone' => 'bg-rose-100 text-rose-700', 'icon' => 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21a8 8 0 0116 0'],
        ['label' => 'Unread messages', 'value' => $unreadCount, 'route' => route('admin.messages.index', ['unreadOnly' => 1]), 'hint' => 'from the contact form', 'tone' => 'bg-orange-100 text-orange-700', 'icon' => 'M3 6h18v12H3zM3 7l9 7 9-7', 'alert' => $unreadCount > 0],
        ['label' => 'Comments to review', 'value' => $pendingCommentCount, 'route' => route('admin.comments.index'), 'hint' => 'awaiting approval', 'tone' => 'bg-teal-100 text-teal-700', 'icon' => 'M4 5h16v11H9l-5 4z', 'alert' => $pendingCommentCount > 0],
    ];
    $peak = max(1, max($dailyViews));
@endphp

<div>
    <section class="relative overflow-hidden rounded-2xl bg-linear-to-br from-brand via-[#3b7364] to-[#1d3d34] p-6 text-white shadow-lg sm:p-8">
        <div class="pointer-events-none absolute -right-10 -top-16 size-56 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-20 right-1/3 size-48 rounded-full bg-amber-300/20 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/70">{{ now()->format('l, j F Y') }}</p>
                <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Welcome back, {{ str(auth()->user()->name)->before(' ') }}</h1>
                <p class="mt-2 max-w-md text-sm text-white/80">
                    @if ($pendingCommentCount + $unreadCount > 0)
                        You have {{ $unreadCount }} unread {{ str('message')->plural($unreadCount) }} and {{ $pendingCommentCount }} {{ str('comment')->plural($pendingCommentCount) }} waiting for review.
                    @else
                        You're all caught up. A good day to write something new.
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-brand shadow-md transition hover:-translate-y-0.5 hover:shadow-lg">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                New article
            </a>
        </div>
    </section>

    <dl class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($stats as $stat)
            @php($alert = $stat['alert'] ?? false)
            <div @class(['card relative flex items-start gap-3 p-4 transition duration-200 sm:p-5', 'hover:-translate-y-1 hover:shadow-lg' => $stat['route'], 'ring-2 ring-brand/40' => $alert])>
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $stat['tone'] }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $stat['icon'] }}"/></svg>
                </span>
                <div class="min-w-0">
                    <dt class="truncate text-xs font-semibold uppercase tracking-wide text-muted">
                        @if ($stat['route'])
                            <a href="{{ $stat['route'] }}" class="after:absolute after:inset-0">{{ $stat['label'] }}</a>
                        @else
                            {{ $stat['label'] }}
                        @endif
                    </dt>
                    <dd class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{{ $stat['value'] }}</dd>
                    <dd class="text-xs text-muted">{{ $stat['hint'] }}</dd>
                </div>
            </div>
        @endforeach

        <div class="card col-span-2 flex flex-col justify-between p-4 sm:p-5">
            <div class="flex items-baseline justify-between">
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Daily views</dt>
                <dd class="text-xs text-muted">last 14 days</dd>
            </div>
            <div class="mt-3 flex h-16 items-end gap-1" role="img" aria-label="Bar chart of daily views over the last 14 days, peak {{ $peak }}">
                @foreach ($dailyViews as $day => $count)
                    <div class="flex-1 rounded-t bg-linear-to-t from-brand to-emerald-400" style="height: {{ max(4, round($count / $peak * 100)) }}%" title="{{ \Illuminate\Support\Carbon::parse($day)->format('j M') }}: {{ $count }}"></div>
                @endforeach
            </div>
        </div>
    </dl>

    <div class="mt-10 grid gap-8 xl:grid-cols-2">
        <section aria-labelledby="top-heading">
            <h2 id="top-heading" class="text-lg font-semibold">Most read, last 30 days</h2>
            <ol class="mt-3 divide-y divide-muted/15 card">
                @forelse ($topArticles as $article)
                    <li class="flex items-center gap-4 px-4 py-3 text-sm transition hover:bg-brand/5">
                        <span @class(['flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold', 'bg-amber-300 text-ink' => $loop->first, 'bg-brand/10 text-brand' => ! $loop->first])>{{ $loop->iteration }}</span>
                        <a href="{{ route('articles.show', $article->slug) }}" class="min-w-0 flex-1 truncate font-medium hover:text-brand">{{ $article->title }}</a>
                        <span class="shrink-0 rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700">{{ number_format($article->recent_views) }} views</span>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-muted">No views recorded yet.</li>
                @endforelse
            </ol>
        </section>

        <section aria-labelledby="recent-heading">
            <div class="flex items-center justify-between">
                <h2 id="recent-heading" class="text-lg font-semibold">Recently edited</h2>
                <a href="{{ route('admin.articles.index') }}" class="text-sm font-medium text-brand hover:underline">All articles</a>
            </div>
            <ul class="mt-3 divide-y divide-muted/15 card">
                @forelse ($recentArticles as $article)
                    <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm transition hover:bg-brand/5">
                        <a href="{{ route('admin.articles.edit', $article) }}" class="min-w-0 flex-1 truncate font-medium hover:text-brand">{{ $article->title }}</a>
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-emerald-100 text-emerald-700' => $article->status === \App\Enums\ArticleStatus::Published,
                            'bg-amber-100 text-amber-700' => $article->status === \App\Enums\ArticleStatus::Draft,
                            'bg-sky-100 text-sky-700' => $article->status === \App\Enums\ArticleStatus::Scheduled,
                        ])>{{ $article->status->label() }}</span>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-muted">No articles yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
