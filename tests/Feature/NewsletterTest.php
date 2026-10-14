<?php

namespace Tests\Feature;

use App\Livewire\Newsletter\Signup;
use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('newsletter:127.0.0.1');
    }

    public function test_signup_creates_a_pending_subscriber_and_sends_confirmation(): void
    {
        Mail::fake();

        Livewire::test(Signup::class)
            ->set('email', 'Reader@Example.test')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $subscriber = Subscriber::where('email', 'reader@example.test')->firstOrFail();
        $this->assertFalse($subscriber->isConfirmed());
        Mail::assertSent(ConfirmSubscription::class, fn (ConfirmSubscription $mail) => $mail->hasTo('reader@example.test'));
    }

    public function test_invalid_email_is_rejected(): void
    {
        Livewire::test(Signup::class)->set('email', 'not-an-email')->call('subscribe')->assertHasErrors('email');

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_duplicate_pending_signup_resends_without_creating_a_second_row(): void
    {
        Mail::fake();

        foreach (range(1, 2) as $attempt) {
            Livewire::test(Signup::class)->set('email', 'reader@example.test')->call('subscribe');
        }

        $this->assertDatabaseCount('subscribers', 1);
        Mail::assertSent(ConfirmSubscription::class, 2);
    }

    public function test_confirmed_subscriber_gets_the_same_response_and_no_email(): void
    {
        Mail::fake();
        Subscriber::factory()->create(['email' => 'reader@example.test']);

        Livewire::test(Signup::class)
            ->set('email', 'reader@example.test')
            ->call('subscribe')
            ->assertSet('submitted', true);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('subscribers', 1);
    }

    public function test_filled_honeypot_is_silently_discarded(): void
    {
        Mail::fake();

        Livewire::test(Signup::class)
            ->set('email', 'bot@example.test')
            ->set('website', 'http://spam.test')
            ->call('subscribe')
            ->assertSet('submitted', true);

        $this->assertDatabaseCount('subscribers', 0);
        Mail::assertNothingSent();
    }

    public function test_signup_is_rate_limited(): void
    {
        Mail::fake();

        foreach (range(1, 5) as $attempt) {
            Livewire::test(Signup::class)->set('email', "reader{$attempt}@example.test")->call('subscribe')->assertHasNoErrors();
        }

        Livewire::test(Signup::class)->set('email', 'extra@example.test')->call('subscribe')->assertHasErrors('email');

        $this->assertDatabaseMissing('subscribers', ['email' => 'extra@example.test']);
    }

    public function test_signed_link_confirms_the_subscription(): void
    {
        $subscriber = Subscriber::factory()->unconfirmed()->create();

        $this->get(URL::signedRoute('newsletter.confirm', ['subscriber' => $subscriber->id]))
            ->assertOk()
            ->assertSee('Subscription confirmed');

        $this->assertTrue($subscriber->fresh()->isConfirmed());
    }

    public function test_unsigned_or_tampered_confirmation_link_is_forbidden(): void
    {
        $subscriber = Subscriber::factory()->unconfirmed()->create();

        $this->get(route('newsletter.confirm', ['subscriber' => $subscriber->id]))->assertForbidden();

        $this->assertFalse($subscriber->fresh()->isConfirmed());
    }

    public function test_unsubscribe_requires_a_post_and_removes_the_subscriber(): void
    {
        $subscriber = Subscriber::factory()->create();

        $this->get(route('newsletter.unsubscribe', $subscriber->unsubscribe_token))
            ->assertOk()
            ->assertSee($subscriber->email);
        $this->assertDatabaseHas('subscribers', ['id' => $subscriber->id]);

        $this->post(route('newsletter.unsubscribe.destroy', $subscriber->unsubscribe_token))
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('subscribers', ['id' => $subscriber->id]);
    }

    public function test_unknown_unsubscribe_token_is_not_found(): void
    {
        $this->get(route('newsletter.unsubscribe', 'unknown'))->assertNotFound();
    }

    public function test_signup_form_appears_on_home_footer_and_articles(): void
    {
        $this->get(route('home'))->assertSeeLivewire(Signup::class);
    }
}
