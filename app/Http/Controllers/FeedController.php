<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Setting;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    private const ITEM_LIMIT = 20;

    public function __invoke(): Response
    {
        $articles = Article::published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $xml = view('seo.feed', [
            'articles' => $articles,
            'tagline' => Setting::get('tagline', 'Business analyst and social researcher'),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
