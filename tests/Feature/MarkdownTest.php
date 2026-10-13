<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Support\Markdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_markdown_is_rendered_to_html(): void
    {
        $html = Markdown::toHtml("# Title\n\nSome **bold** text.");

        $this->assertStringContainsString('<h1>Title</h1>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function test_raw_html_and_unsafe_links_are_neutralised(): void
    {
        $html = Markdown::toHtml("<script>alert(1)</script>\n\n[click](javascript:alert(1))\n\n<img src=x onerror=alert(1)>");

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onerror', $html);
    }

    public function test_article_exposes_rendered_body(): void
    {
        $article = Article::factory()->create(['body' => 'Hello *world*']);

        $this->assertStringContainsString('<em>world</em>', $article->body_html);
    }
}
