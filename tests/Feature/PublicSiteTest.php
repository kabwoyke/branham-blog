<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_featured_latest_and_category_highlights(): void
    {
        $featured = Article::factory()->featured()->create(['title' => 'Featured headline']);
        $latest = Article::factory()->create(['title' => 'Latest headline']);
        $category = Category::factory()->create(['name' => 'Highlighted topic']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Featured', 'Featured headline'])
            ->assertSee('Latest headline')
            ->assertSee('Highlighted topic')
            ->assertSee(route('articles.show', $featured->slug), false)
            ->assertSee(route('articles.show', $latest->slug), false);
    }

    public function test_home_does_not_list_unpublished_articles(): void
    {
        Article::factory()->draft()->create(['title' => 'Secret draft']);
        Article::factory()->scheduled()->create(['title' => 'Future piece']);

        $this->get(route('home'))->assertOk()->assertDontSee('Secret draft')->assertDontSee('Future piece');
    }

    public function test_listing_is_paginated_newest_first(): void
    {
        Article::factory()->count(12)->sequence(fn ($sequence) => [
            'title' => 'Article number '.$sequence->index,
            'published_at' => now()->subDays(20 - $sequence->index),
        ])->create();

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSeeInOrder(['Article number 11', 'Article number 10'])
            ->assertDontSee('Article number 0<', false)
            ->assertSee('page=2');

        $this->get(route('articles.index', ['page' => 2]))->assertOk()->assertSee('Article number 0');
    }

    public function test_listing_hides_drafts_and_future_dated_articles(): void
    {
        Article::factory()->create(['title' => 'Visible piece']);
        Article::factory()->draft()->create(['title' => 'Hidden draft']);
        Article::factory()->scheduled()->create(['title' => 'Hidden scheduled']);

        $this->get(route('articles.index'))->assertSee('Visible piece')->assertDontSee('Hidden draft')->assertDontSee('Hidden scheduled');
    }

    public function test_listing_shows_empty_state(): void
    {
        $this->get(route('articles.index'))->assertOk()->assertSee('No articles found');
    }

    public function test_category_page_lists_only_its_articles(): void
    {
        $category = Category::factory()->create(['name' => 'Trust topic', 'slug' => 'trust-topic']);
        Article::factory()->for($category)->create(['title' => 'In the category']);
        Article::factory()->create(['title' => 'Somewhere else']);

        $this->get(route('categories.show', 'trust-topic'))
            ->assertOk()
            ->assertSee('Trust topic')
            ->assertSee('In the category')
            ->assertDontSee('Somewhere else');
    }

    public function test_unknown_category_returns_404(): void
    {
        $this->get('/category/does-not-exist')->assertNotFound();
    }

    public function test_published_article_page_shows_details_and_share_links(): void
    {
        $author = User::factory()->create(['name' => 'Branham Samwel']);
        $article = Article::factory()->for($author, 'author')->create([
            'title' => 'A readable article',
            'body' => "Opening paragraph.\n\n## A subheading",
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('A readable article')
            ->assertSee('Branham Samwel')
            ->assertSee($article->published_at->format('j F Y'))
            ->assertSee('min read')
            ->assertSee('<h2>A subheading</h2>', false)
            ->assertSee('linkedin.com/sharing/share-offsite', false)
            ->assertSee('mailto:?subject=', false)
            ->assertSee('Copy link')
            ->assertDontSee('Preview:');
    }

    public function test_featured_image_is_shown_with_alt_text(): void
    {
        $article = Article::factory()->create([
            'featured_image' => 'articles/photo.webp',
            'featured_image_alt' => 'A quiet street',
        ]);

        $this->get(route('articles.show', $article->slug))
            ->assertSee('alt="A quiet street"', false)
            ->assertSee('articles/photo.webp', false);
    }

    public function test_unpublished_articles_return_404_for_guests(): void
    {
        $draft = Article::factory()->draft()->create();
        $scheduled = Article::factory()->scheduled()->create();
        $futurePublished = Article::factory()->create(['published_at' => now()->addDay()]);

        foreach ([$draft, $scheduled, $futurePublished] as $article) {
            $this->get(route('articles.show', $article->slug))->assertNotFound();
        }
    }

    public function test_soft_deleted_articles_return_404_even_for_admin(): void
    {
        $article = Article::factory()->create();
        $article->delete();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('articles.show', $article->slug))
            ->assertNotFound();
    }

    public function test_admin_can_preview_a_draft(): void
    {
        $draft = Article::factory()->draft()->create(['title' => 'Draft for preview']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('articles.show', $draft->slug))
            ->assertOk()
            ->assertSee('Draft for preview')
            ->assertSee('Preview:');
    }

    public function test_non_admin_users_cannot_preview_drafts(): void
    {
        $draft = Article::factory()->draft()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('articles.show', $draft->slug))
            ->assertNotFound();
    }

    public function test_related_articles_prefer_same_category_then_recent_others(): void
    {
        $category = Category::factory()->create();
        $article = Article::factory()->for($category)->create(['title' => 'Main piece']);
        $sibling = Article::factory()->for($category)->create(['title' => 'Same category sibling']);
        $other = Article::factory()->create(['title' => 'Other category piece', 'published_at' => now()->subDay()]);
        Article::factory()->draft()->for($category)->create(['title' => 'Draft sibling']);

        $related = $article->related();

        $this->assertSame([$sibling->id, $other->id], $related->pluck('id')->all());
        $this->assertFalse($related->contains($article));

        $this->get(route('articles.show', $article->slug))
            ->assertSee('Related articles')
            ->assertSee('Same category sibling')
            ->assertDontSee('Draft sibling');
    }

    public function test_related_section_hidden_when_there_is_nothing_related(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article->slug))->assertDontSee('Related articles');
    }

    public function test_article_meta_overrides_are_used(): void
    {
        $article = Article::factory()->create([
            'meta_title' => 'Custom meta title',
            'meta_description' => 'Custom meta description',
        ]);

        $this->get(route('articles.show', $article->slug))
            ->assertSee('<title>Custom meta title | Branham Samwel</title>', false)
            ->assertSee('content="Custom meta description"', false);
    }

    public function test_article_body_cannot_inject_scripts(): void
    {
        $article = Article::factory()->create(['body' => "Safe text\n\n<script>alert('x')</script>"]);

        $this->get(route('articles.show', $article->slug))->assertOk()->assertDontSee("<script>alert('x')", false);
    }

    public function test_about_page_renders_markdown_from_settings(): void
    {
        Setting::set('about_content', "Hello **world**.\n\n<script>bad()</script>");

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('<strong>world</strong>', false)
            ->assertDontSee('<script>bad()', false);
    }

    public function test_footer_shows_contact_details_from_settings(): void
    {
        Setting::set('contact_email', 'hello@example.test');
        Setting::set('linkedin_url', 'https://www.linkedin.com/in/example');

        $this->get(route('home'))
            ->assertSee('mailto:hello@example.test', false)
            ->assertSee('https://www.linkedin.com/in/example', false);
    }

    public function test_friendly_404_page_uses_the_site_design(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Back to home')
            ->assertSee('Branham Samwel');
    }
}
