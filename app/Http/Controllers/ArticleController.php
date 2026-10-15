<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\Markdown;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    public const PER_PAGE = 9;

    public function featured(): View
    {
        return view('articles.featured', [
            'articles' => Article::published()
                ->featured()
                ->with(['category', 'author'])
                ->latest('published_at')
                ->paginate(self::PER_PAGE),
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
            'previous' => $article->isPublic() ? $article->previousArticle() : null,
            'next' => $article->isPublic() ? $article->nextArticle() : null,
            'headings' => Markdown::headings($article->body_html),
            'isPreview' => ! $article->isPublic(),
        ]);
    }
}
