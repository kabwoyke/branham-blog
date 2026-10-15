<?php

namespace Tests\Feature;

use App\Models\Article;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_send_security_headers(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $response = $this->get('https://localhost/');

        $response->assertHeader('Strict-Transport-Security');
    }

    public function test_security_headers_are_present_on_error_pages(): void
    {
        $this->get('/articles/does-not-exist')
            ->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_article_body_cannot_inject_scripts(): void
    {
        $article = Article::factory()->create([
            'body' => "<script>alert('xss')</script>\n\nSafe paragraph.",
        ]);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Safe paragraph.')
            ->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_production_seeding_creates_no_demo_articles(): void
    {
        config([
            'blog.admin.email' => 'admin@example.test',
            'blog.admin.password' => 'secret-password',
        ]);
        $this->app['env'] = 'production';

        (new DatabaseSeeder)->setContainer($this->app)->run();

        $this->assertSame(0, Article::count());
    }
}
