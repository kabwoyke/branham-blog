<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        return view('articles.index', [
            'articles' => Article::published()
                ->inCategory($category)
                ->with(['category', 'author'])
                ->latest('published_at')
                ->paginate(ArticleController::PER_PAGE),
            'categories' => Category::orderBy('id')->get(),
            'category' => $category,
        ]);
    }
}
