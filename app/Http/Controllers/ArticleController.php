<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    public const PER_PAGE = 9;

    public function index(): View
    {
        return view('articles.index', [
            'articles' => Article::published()
                ->with(['category', 'author'])
                ->latest('published_at')
                ->paginate(self::PER_PAGE),
            'categories' => Category::orderBy('id')->get(),
            'category' => null,
        ]);
    }

    /**
     * Guests only ever see published articles. The admin may preview drafts and scheduled articles.
     */
    public function show(string $slug): View
    {
        $article = Article::query()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->when(! Gate::allows('access-admin'), fn ($query) => $query->published())
            ->firstOrFail();

        return view('articles.show', [
            'article' => $article,
            'related' => $article->related(),
            'isPreview' => ! $article->isPublic(),
        ]);
    }
}
