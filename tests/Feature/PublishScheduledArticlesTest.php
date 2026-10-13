<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishScheduledArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_scheduled_articles_are_published(): void
    {
        $due = Article::factory()->create(['status' => ArticleStatus::Scheduled, 'published_at' => now()->subMinute()]);
        $future = Article::factory()->scheduled()->create();
        $draft = Article::factory()->draft()->create();

        $this->artisan('articles:publish-scheduled')->assertSuccessful();

        $this->assertSame(ArticleStatus::Published, $due->fresh()->status);
        $this->assertSame(ArticleStatus::Scheduled, $future->fresh()->status);
        $this->assertSame(ArticleStatus::Draft, $draft->fresh()->status);
    }

    public function test_command_is_registered_with_the_scheduler(): void
    {
        $commands = collect(app(Schedule::class)->events())->pluck('command')->implode(' ');

        $this->assertStringContainsString('articles:publish-scheduled', $commands);
    }
}
