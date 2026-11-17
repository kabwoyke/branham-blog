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

    public function test_image_with_a_title_becomes_a_captioned_figure(): void
    {
        $html = Markdown::toHtml('![A busy street](/storage/a.webp "Rush hour, 2026")');

        $this->assertStringContainsString('<figure><img', $html);
        $this->assertStringContainsString('alt="A busy street"', $html);
        $this->assertStringContainsString('<figcaption>Rush hour, 2026</figcaption></figure>', $html);
        $this->assertStringNotContainsString('title=', $html);
    }

    public function test_image_without_a_caption_stays_a_plain_lazy_image(): void
    {
        $html = Markdown::toHtml('![Plain](/storage/b.webp)');

        $this->assertStringNotContainsString('<figure', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
    }

    public function test_captions_are_escaped(): void
    {
        $html = Markdown::toHtml('![x](/a.webp "<b>bold</b> & more")');

        $this->assertStringNotContainsString('<b>', $html);
    }

    public function test_article_exposes_rendered_body(): void
    {
        $article = Article::factory()->create(['body' => 'Hello *world*']);

        $this->assertStringContainsString('<em>world</em>', $article->body_html);
    }

    public function test_sections_are_listed_from_h2_and_h3_headings_outside_code(): void
    {
        $markdown = "Intro\n\n## Trust & Power\n\nText\n\n```\n## Not a heading\n```\n\n### Detail\n\n## Trust & Power";

        $sections = Markdown::sections($markdown);

        $this->assertSame(['trust-power', 'detail', 'trust-power-2'], array_column($sections, 'key'));
        $this->assertSame([2, 3, 2], array_column($sections, 'level'));
        $this->assertStringContainsString('id="trust-power-2"', Markdown::toHtml($markdown));
    }

    public function test_snippets_are_inserted_at_the_chosen_place(): void
    {
        $markdown = "Intro\n\n## One\n\nFirst.\n\n### Sub\n\nDeeper.\n\n## Two\n\nSecond.";

        $this->assertStringStartsWith("IMG\n\nIntro\n\n## One", Markdown::insertAt($markdown, 'start', 'IMG'));
        $this->assertStringEndsWith("Second.\n\nIMG\n", Markdown::insertAt($markdown, 'end', 'IMG'));
        $this->assertStringContainsString("## One\n\nIMG\n\nFirst.", Markdown::insertAt($markdown, 'below:one', 'IMG'));
        $this->assertStringContainsString("Deeper.\n\nIMG\n\n## Two", Markdown::insertAt($markdown, 'section-end:one', 'IMG'));
        $this->assertStringContainsString("Deeper.\n\nIMG\n\n## Two", Markdown::insertAt($markdown, 'section-end:sub', 'IMG'));
        $this->assertStringEndsWith("Second.\n\nIMG\n", Markdown::insertAt($markdown, 'section-end:two', 'IMG'));
    }
}
