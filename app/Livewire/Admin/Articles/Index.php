<?php

namespace App\Livewire\Admin\Articles;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
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
