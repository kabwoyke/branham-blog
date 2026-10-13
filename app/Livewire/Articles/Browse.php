<?php

namespace App\Livewire\Articles;

use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Support\ArticleSearch;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The public article list: searchable and filterable, with state kept in the URL.
 *
 * The category comes from the route (/category/{slug}) so category pages keep clean, indexable addresses.
 */
class Browse extends Component
{
    use WithPagination;

    public const PER_PAGE = 9;

    #[Locked]
    public ?Category $category = null;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $type = '';

    public function mount(?Category $category = null): void
    {
        $this->category = $category;
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr($this->search, 0, ArticleSearch::MAX_TERM_LENGTH);
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'type');
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'pagination.editorial-livewire';
    }

    public function render(): View
    {
        $type = ArticleType::tryFrom($this->type);

        $articles = Article::published()
            ->with(['category', 'author'])
            ->when($this->category, fn ($query, Category $category) => $query->inCategory($category))
            ->when($type, fn ($query, ArticleType $type) => $query->where('type', $type))
            ->when(trim($this->search) !== '', fn ($query) => ArticleSearch::apply($query, $this->search))
            ->latest('published_at')
            ->paginate(self::PER_PAGE)
            ->withPath($this->category ? route('categories.show', $this->category) : route('articles.index'))
            ->appends(array_filter(['q' => trim($this->search) ?: null, 'type' => $type?->value]));

        $heading = $this->category?->name ?? 'Articles';
        $intro = $this->category?->description ?? 'Observations, perspectives and predictions on society, business and the future.';

        return view('livewire.articles.browse', [
            'articles' => $articles,
            'categories' => Category::orderBy('id')->get(),
            'types' => ArticleType::cases(),
            'heading' => $heading,
            'intro' => $intro,
            'isFiltered' => trim($this->search) !== '' || $type !== null,
        ])->layout('components.layouts.app', ['title' => $heading, 'description' => $intro]);
    }
}
