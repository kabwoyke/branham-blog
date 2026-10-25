<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleViewsTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0) Firefox/130.0';

    public function test_a_visit_is_counted_once_per_session(): void
    {
        $article = Article::factory()->create();

        $this->withHeader('User-Agent', self::BROWSER)->get(route('articles.show', $article->slug));
        $this->get(route('articles.show', $article->slug));

        $this->assertSame(1, (int) ArticleView::where('article_id', $article->id)->sum('views'));
    }

    public function test_bots_are_not_counted(): void
    {
        $article = Article::factory()->create();

        $this->withHeader('User-Agent', 'Googlebot/2.1')->get(route('articles.show', $article->slug));

        $this->assertSame(0, ArticleView::count());
    }

    public function test_admin_previews_and_drafts_are_not_counted(): void
    {
        $article = Article::factory()->create();
        $draft = Article::factory()->draft()->create();

        $this->actingAs(User::factory()->admin()->create())->withHeader('User-Agent', self::BROWSER);
        $this->get(route('articles.show', $article->slug));
        $this->get(route('articles.show', $draft->slug));

        $this->assertSame(0, ArticleView::count());
    }

    public function test_dashboard_shows_recent_views(): void
    {
        $article = Article::factory()->create(['title' => 'Popular piece']);
        ArticleView::create(['article_id' => $article->id, 'viewed_on' => today(), 'views' => 42]);
        ArticleView::create(['article_id' => $article->id, 'viewed_on' => today()->subDays(60), 'views' => 1000]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('Popular piece')
            ->assertSee('42 views');
    }
}
