<?php

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Mail\ArticleNewsletter;
use App\Models\Article;
use App\Models\Category;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Articles')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    #[Url]
    public bool $trashed = false;

    public function mount(): void
    {
        Gate::authorize('viewAny', Article::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'category', 'trashed'], true)) {
            $this->resetPage();
        }
    }

    public function delete(int $articleId): void
    {
        $article = Article::findOrFail($articleId);
        Gate::authorize('delete', $article);

        $article->delete();

        session()->flash('status', "\"{$article->title}\" moved to trash.");
    }

    public function restore(int $articleId): void
    {
        $article = Article::onlyTrashed()->findOrFail($articleId);
        Gate::authorize('restore', $article);

        $article->restore();

        session()->flash('status', "\"{$article->title}\" restored.");
    }

    public function sendNewsletter(int $articleId): void
    {
        $article = Article::published()->with('category')->findOrFail($articleId);
        Gate::authorize('update', $article);

        if ($article->newsletter_sent_at !== null) {
            session()->flash('status', "\"{$article->title}\" was already sent to subscribers.");

            return;
        }

        $sent = 0;

        Subscriber::confirmed()->each(function (Subscriber $subscriber) use ($article, &$sent): void {
            Mail::to($subscriber->email)->queue(new ArticleNewsletter($article, $subscriber));
            $sent++;
        });

        $article->forceFill(['newsletter_sent_at' => now()])->saveQuietly();

        session()->flash('status', "\"{$article->title}\" queued for {$sent} ".str('subscriber')->plural($sent).'.');
    }

    public function render(): View
    {
        $articles = Article::query()
            ->with('category')
            ->when($this->trashed, fn ($query) => $query->onlyTrashed())
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when(ArticleStatus::tryFrom($this->status), fn ($query, $status) => $query->where('status', $status))
            ->when($this->category !== '', fn ($query) => $query->where('category_id', (int) $this->category))
            ->latest('updated_at')
            ->paginate(15);

        return view('livewire.admin.articles.index', [
            'articles' => $articles,
            'categories' => Category::orderBy('name')->get(),
            'statuses' => ArticleStatus::cases(),
        ]);
    }
}
