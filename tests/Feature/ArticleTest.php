<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_belongs_to_author_and_category(): void
    {
        $article = Article::factory()->create();

        $this->assertInstanceOf(User::class, $article->author);
        $this->assertInstanceOf(Category::class, $article->category);
        $this->assertTrue($article->category->articles->contains($article));
    }

    public function test_published_scope_excludes_drafts_and_future_dated_articles(): void
    {
        $visible = Article::factory()->create();
        Article::factory()->draft()->create();
        Article::factory()->scheduled()->create();
        Article::factory()->create([
            'status' => ArticleStatus::Published,
            'published_at' => now()->addDay(),
        ]);

        $this->assertSame([$visible->id], Article::published()->pluck('id')->all());
    }

    public function test_featured_scope(): void
    {
        $featured = Article::factory()->featured()->create();
        Article::factory()->create();

        $this->assertSame([$featured->id], Article::featured()->pluck('id')->all());
    }

    public function test_in_category_scope_accepts_model_id_and_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'trust']);
        $inCategory = Article::factory()->for($category)->create();
        Article::factory()->create();

        $this->assertSame([$inCategory->id], Article::inCategory($category)->pluck('id')->all());
        $this->assertSame([$inCategory->id], Article::inCategory($category->id)->pluck('id')->all());
        $this->assertSame([$inCategory->id], Article::inCategory('trust')->pluck('id')->all());
    }

    public function test_type_and_status_are_cast_to_enums(): void
    {
        $article = Article::factory()->ofType(ArticleType::Research)->create();

        $this->assertSame(ArticleType::Research, $article->fresh()->type);
        $this->assertSame(ArticleStatus::Published, $article->fresh()->status);
    }

    public function test_reading_time_is_computed_from_body_and_never_below_one_minute(): void
    {
        $short = Article::factory()->create(['body' => 'A few words.']);
        $long = Article::factory()->create(['body' => implode(' ', array_fill(0, 650, 'word'))]);

        $this->assertSame(1, $short->reading_time);
        $this->assertSame(4, $long->reading_time);
    }

    public function test_articles_are_soft_deleted_and_can_be_restored(): void
    {
        $article = Article::factory()->create();

        $article->delete();
        $this->assertSoftDeleted($article);

        $article->restore();
        $this->assertNotSoftDeleted($article);
    }

    public function test_articles_resolve_by_slug(): void
    {
        $this->assertSame('slug', (new Article)->getRouteKeyName());
        $this->assertSame('slug', (new Category)->getRouteKeyName());
    }

    public function test_settings_are_stored_and_read_with_defaults(): void
    {
        Setting::set('tagline', 'First');
        $this->assertSame('First', Setting::get('tagline'));

        Setting::set('tagline', 'Second');
        $this->assertSame('Second', Setting::get('tagline'));
        $this->assertSame('fallback', Setting::get('missing', 'fallback'));
    }
}
