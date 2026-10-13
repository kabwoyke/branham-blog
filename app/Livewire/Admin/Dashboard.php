<?php

namespace App\Livewire\Admin;

use App\Enums\ArticleStatus;
use App\Models\Article;
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
            'recentArticles' => Article::with('category')->latest('updated_at')->limit(5)->get(),
        ]);
    }
}
