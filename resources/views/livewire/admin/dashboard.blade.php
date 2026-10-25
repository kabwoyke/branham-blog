<div>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">{{ now()->format('l, j F Y') }}</p>
            <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Welcome back, {{ str(auth()->user()->name)->before(' ') }}</h1>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">New article</a>
    </div>

    @php
        $stats = [
            ['label' => 'Published', 'value' => $publishedCount, 'route' => route('admin.articles.index', ['status' => 'published']), 'hint' => 'live articles'],
            ['label' => 'Drafts', 'value' => $draftCount, 'route' => route('admin.articles.index', ['status' => 'draft']), 'hint' => 'in progress'],
            ['label' => 'Scheduled', 'value' => $scheduledCount, 'route' => route('admin.articles.index', ['status' => 'scheduled']), 'hint' => 'queued to publish'],
            ['label' => 'Views', 'value' => number_format($viewsLast30Days), 'route' => null, 'hint' => 'last 30 days'],
            ['label' => 'Subscribers', 'value' => $subscriberCount, 'route' => route('admin.subscribers.index'), 'hint' => 'confirmed'],
            ['label' => 'Unread messages', 'value' => $unreadCount, 'route' => route('admin.messages.index', ['unreadOnly' => 1]), 'hint' => 'from the contact form', 'alert' => $unreadCount > 0],
            ['label' => 'Comments to review', 'value' => $pendingCommentCount, 'route' => route('admin.comments.index'), 'hint' => 'awaiting approval', 'alert' => $pendingCommentCount > 0],
        ];
    @endphp

    <dl class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($stats as $stat)
            @php($alert = $stat['alert'] ?? false)
            <div @class(['card relative p-5 transition', 'hover:-translate-y-0.5 hover:shadow-md' => $stat['route'], 'border-brand/50 bg-brand/5' => $alert])>
                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">
                    @if ($stat['route'])
                        <a href="{{ $stat['route'] }}" class="after:absolute after:inset-0">{{ $stat['label'] }}</a>
                    @else
                        {{ $stat['label'] }}
                    @endif
                </dt>
                <dd @class(['mt-2 text-3xl font-bold tracking-tight', 'text-brand' => $alert])>{{ $stat['value'] }}</dd>
                <dd class="mt-1 text-xs text-muted">{{ $stat['hint'] }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="mt-10 grid gap-8 xl:grid-cols-2">
        <section aria-labelledby="top-heading">
            <h2 id="top-heading" class="text-lg font-semibold">Most read, last 30 days</h2>
            <ol class="mt-3 divide-y divide-muted/15 card">
                @forelse ($topArticles as $article)
                    <li class="flex items-center gap-4 px-4 py-3 text-sm">
                        <span class="w-5 text-center font-bold text-brand">{{ $loop->iteration }}</span>
                        <a href="{{ route('articles.show', $article->slug) }}" class="min-w-0 flex-1 truncate font-medium hover:text-brand">{{ $article->title }}</a>
                        <span class="shrink-0 text-muted">{{ number_format($article->recent_views) }} views</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-muted">No views recorded yet.</li>
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
                    <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                        <a href="{{ route('admin.articles.edit', $article) }}" class="min-w-0 flex-1 truncate font-medium hover:text-brand">{{ $article->title }}</a>
                        <span class="shrink-0 text-xs text-muted">{{ $article->status->label() }} · {{ $article->category->name }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-muted">No articles yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
