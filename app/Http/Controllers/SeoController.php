<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    private const SITEMAP_TTL_SECONDS = 3600;

    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap-xml', self::SITEMAP_TTL_SECONDS, function (): string {
            $pages = collect(['home', 'articles.index', 'articles.featured', 'about', 'contact'])
                ->map(fn (string $name): array => ['loc' => route($name), 'lastmod' => null]);

            $categories = Category::query()->orderBy('id')->get()
                ->map(fn (Category $category): array => ['loc' => route('categories.show', $category->slug), 'lastmod' => null]);

            $articles = Article::published()->latest('published_at')->get(['slug', 'updated_at'])
                ->map(fn (Article $article): array => [
                    'loc' => route('articles.show', $article->slug),
                    'lastmod' => $article->updated_at?->toAtomString(),
                ]);

            return view('seo.sitemap', ['urls' => $pages->concat($categories)->concat($articles)])->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /newsletter/',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
