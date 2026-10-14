<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_page_has_canonical_open_graph_and_structured_data(): void
    {
        $article = Article::factory()->create(['meta_description' => 'A custom description.']);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('articles.show', $article->slug).'">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="og:description" content="A custom description.">', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_home_page_has_website_structured_data(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('rel="canonical"', false);
    }

    public function test_draft_preview_is_not_indexed(): void
    {
        $article = Article::factory()->draft()->create();

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('noindex', false);
    }

    public function test_sitemap_lists_only_published_articles(): void
    {
        $published = Article::factory()->create();
        $draft = Article::factory()->draft()->create();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('articles.show', $published->slug), false)
            ->assertDontSee(route('articles.show', $draft->slug), false);
    }

    public function test_robots_txt_points_to_sitemap_and_blocks_admin(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_rss_feed_lists_published_articles(): void
    {
        $published = Article::factory()->create();
        $scheduled = Article::factory()->scheduled()->create();

        $this->get(route('feed'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->assertSee(route('articles.show', $published->slug), false)
            ->assertDontSee(route('articles.show', $scheduled->slug), false);
    }
}
