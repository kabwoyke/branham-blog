<?php

namespace App\Livewire\Admin;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'publishedCount' => Article::where('status', ArticleStatus::Published)->count(),
            'draftCount' => Article::where('status', ArticleStatus::Draft)->count(),
            'scheduledCount' => Article::where('status', ArticleStatus::Scheduled)->count(),
            'subscriberCount' => Subscriber::confirmed()->count(),
            'unreadCount' => ContactMessage::unread()->count(),
            'dailyViews' => $this->dailyViews(),
            'pendingCommentCount' => Comment::pending()->count(),
            'viewsLast30Days' => (int) ArticleView::where('viewed_on', '>=', today()->subDays(29))->sum('views'),
            'topArticles' => Article::query()
                ->withSum(['views as recent_views' => fn ($query) => $query->where('viewed_on', '>=', today()->subDays(29))], 'views')
                ->orderByDesc('recent_views')
                ->whereHas('views', fn ($query) => $query->where('viewed_on', '>=', today()->subDays(29)))
                ->limit(5)
                ->get(),
            'recentArticles' => Article::with('category')->latest('updated_at')->limit(5)->get(),
        ]);
    }

    /**
     * @return array<string, int> Views per day for the last 14 days, oldest first.
     */
    private function dailyViews(): array
    {
        $counts = ArticleView::query()
            ->where('viewed_on', '>=', today()->subDays(13))
            ->selectRaw('viewed_on, sum(views) as total')
            ->groupBy('viewed_on')
            ->pluck('total', 'viewed_on')
            ->mapWithKeys(fn ($total, $day) => [substr((string) $day, 0, 10) => (int) $total]);

        return collect(range(13, 0))
            ->mapWithKeys(fn (int $ago) => [today()->subDays($ago)->toDateString() => $counts->get(today()->subDays($ago)->toDateString(), 0)])
            ->all();
    }
}
