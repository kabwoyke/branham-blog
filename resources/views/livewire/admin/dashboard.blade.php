<div>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">Dashboard</h1>
        <a href="{{ route('admin.articles.create') }}" class="rounded-lg bg-brand px-4 py-2 shadow-sm text-sm font-medium text-white hover:bg-brand/90">New article</a>
    </div>

    <dl class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-7">
        @foreach ([['Published', $publishedCount], ['Drafts', $draftCount], ['Scheduled', $scheduledCount], ['Subscribers', $subscriberCount], ['Unread messages', $unreadCount], ['Comments to review', $pendingCommentCount], ['Views (30 days)', $viewsLast30Days]] as [$label, $count])
            <div class="card p-4">
                <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                <dd class="mt-1 text-3xl">{{ $count }}</dd>
            </div>
        @endforeach
    </dl>

    <h2 class="mt-10 text-xl font-semibold">Most read, last 30 days</h2>
    <ul class="mt-3 divide-y divide-muted/20 card">
        @forelse ($topArticles as $article)
            <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                <a href="{{ route('articles.show', $article->slug) }}" class="font-medium hover:text-brand">{{ $article->title }}</a>
                <span class="shrink-0 text-muted">{{ number_format($article->recent_views) }} views</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-muted">No views recorded yet.</li>
        @endforelse
    </ul>

    <h2 class="mt-10 text-xl font-semibold">Recently edited</h2>
    <ul class="mt-3 divide-y divide-muted/20 card">
        @forelse ($recentArticles as $article)
            <li class="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                <a href="{{ route('admin.articles.edit', $article) }}" class="font-medium hover:text-brand">{{ $article->title }}</a>
                <span class="text-muted">{{ $article->status->label() }} · {{ $article->category->name }}</span>
            </li>
        @empty
            <li class="px-4 py-3 text-sm text-muted">No articles yet.</li>
        @endforelse
    </ul>
</div>
