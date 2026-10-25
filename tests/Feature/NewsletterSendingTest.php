<?php

namespace Tests\Feature;

use App\Livewire\Admin\Articles\Index;
use App\Mail\ArticleNewsletter;
use App\Models\Article;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterSendingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_published_article_is_queued_to_confirmed_subscribers_only(): void
    {
        Mail::fake();
        $article = Article::factory()->create();
        $confirmed = Subscriber::factory()->create();
        Subscriber::factory()->unconfirmed()->create();

        Livewire::test(Index::class)->call('sendNewsletter', $article->id);

        Mail::assertQueued(ArticleNewsletter::class, 1);
        Mail::assertQueued(ArticleNewsletter::class, fn (ArticleNewsletter $mail) => $mail->hasTo($confirmed->email));
        $this->assertNotNull($article->fresh()->newsletter_sent_at);
    }

    public function test_article_cannot_be_sent_twice(): void
    {
        Mail::fake();
        $article = Article::factory()->create();
        Subscriber::factory()->create();

        Livewire::test(Index::class)
            ->call('sendNewsletter', $article->id)
            ->call('sendNewsletter', $article->id);

        Mail::assertQueued(ArticleNewsletter::class, 1);
    }

    public function test_drafts_cannot_be_sent(): void
    {
        Mail::fake();
        $draft = Article::factory()->draft()->create();
        Subscriber::factory()->create();

        Livewire::test(Index::class)->call('sendNewsletter', $draft->id)->assertNotFound();

        Mail::assertNothingQueued();
    }

    public function test_newsletter_renders_article_link_and_unsubscribe_link(): void
    {
        $article = Article::factory()->create();
        $subscriber = Subscriber::factory()->create();

        $html = (new ArticleNewsletter($article, $subscriber))->render();

        $this->assertStringContainsString(route('articles.show', $article->slug), $html);
        $this->assertStringContainsString(route('newsletter.unsubscribe', $subscriber->unsubscribe_token), $html);
    }
}
