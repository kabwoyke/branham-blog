<?php

namespace Tests\Feature;

use App\Livewire\Admin\Comments\Index as AdminComments;
use App\Livewire\Comments\Section;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('comment:127.0.0.1');
    }

    public function test_visitor_comment_is_held_for_approval(): void
    {
        $article = Article::factory()->create();

        Livewire::test(Section::class, ['articleId' => $article->id])
            ->set('name', 'Reader')
            ->set('email', 'reader@example.test')
            ->set('body', 'A thoughtful remark.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $comment = Comment::firstOrFail();
        $this->assertFalse($comment->isApproved());
        $this->assertSame($article->id, $comment->article_id);
    }

    public function test_only_approved_comments_are_shown_and_emails_stay_private(): void
    {
        $article = Article::factory()->create();
        Comment::factory()->for($article)->create(['body' => 'Visible remark', 'email' => 'secret@example.test']);
        Comment::factory()->for($article)->pending()->create(['body' => 'Hidden remark']);

        $this->get(route('articles.show', $article->slug))
            ->assertSee('Visible remark')
            ->assertDontSee('Hidden remark')
            ->assertDontSee('secret@example.test');
    }

    public function test_comment_html_is_escaped(): void
    {
        $article = Article::factory()->create();
        Comment::factory()->for($article)->create(['body' => '<script>alert(1)</script>']);

        $this->get(route('articles.show', $article->slug))
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_validation_and_honeypot(): void
    {
        $article = Article::factory()->create();

        Livewire::test(Section::class, ['articleId' => $article->id])
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'body']);

        Livewire::test(Section::class, ['articleId' => $article->id])
            ->set('name', 'Bot')
            ->set('email', 'bot@example.test')
            ->set('body', 'Buy cheap things')
            ->set('website', 'http://spam.test')
            ->call('submit');

        $this->assertSame(0, Comment::count());
    }

    public function test_commenting_is_rate_limited(): void
    {
        $article = Article::factory()->create();

        foreach (range(1, 4) as $attempt) {
            Livewire::test(Section::class, ['articleId' => $article->id])
                ->set('name', 'Reader')
                ->set('email', 'reader@example.test')
                ->set('body', "Remark number {$attempt}")
                ->call('submit');
        }

        $this->assertSame(3, Comment::count());
    }

    public function test_drafts_cannot_receive_comments(): void
    {
        $draft = Article::factory()->draft()->create();

        Livewire::test(Section::class, ['articleId' => $draft->id])
            ->set('name', 'Reader')
            ->set('email', 'reader@example.test')
            ->set('body', 'Early remark')
            ->call('submit')
            ->assertNotFound();
    }

    public function test_admin_can_approve_and_delete_but_guests_cannot_view(): void
    {
        $this->get(route('admin.comments.index'))->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->admin()->create());
        $comment = Comment::factory()->pending()->create();

        Livewire::test(AdminComments::class)
            ->assertSee($comment->body)
            ->call('approve', $comment->id);
        $this->assertTrue($comment->fresh()->isApproved());

        Livewire::test(AdminComments::class)->call('delete', $comment->id);
        $this->assertModelMissing($comment);
    }
}
