<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Article::published()
            ->featured()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $latest = Article::published()
            ->with(['category', 'author'])
            ->whereNotIn('id', $featured->pluck('id'))
            ->latest('published_at')
            ->limit(6)
            ->get();

        $categories = Category::query()
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->orderBy('id')
            ->get();

        return view('home', [
            'featured' => $featured,
            'latest' => $latest,
            'categories' => $categories,
            'tagline' => Setting::get('tagline', 'Business analyst and social researcher'),
        ]);
    }
}
