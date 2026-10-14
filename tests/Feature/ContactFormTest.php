<?php

namespace Tests\Feature;

use App\Livewire\Contact\Form;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('contact:127.0.0.1');
    }

    /**
     * @return array<string, string>
     */
    private function validInput(): array
    {
        return [
            'name' => 'Reader One',
            'email' => 'reader@example.test',
            'subject' => 'A question',
            'message' => 'I enjoyed your latest article very much.',
        ];
    }

    public function test_contact_page_renders_details(): void
    {
        Setting::set('contact_email', 'hello@example.test');
        Setting::set('contact_phone', '+254111429478');
        Setting::set('linkedin_url', 'https://www.linkedin.com/in/example');

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('hello@example.test')
            ->assertSee('https://wa.me/254111429478', false)
            ->assertSee('https://www.linkedin.com/in/example', false);
    }

    public function test_whatsapp_link_is_hidden_until_a_country_code_is_set(): void
    {
        Setting::set('contact_phone', '0111429478');

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('0111429478')
            ->assertDontSee('wa.me');
    }

    public function test_message_is_saved_and_emailed(): void
    {
        Mail::fake();
        Setting::set('contact_email', 'hello@example.test');

        Livewire::test(Form::class)
            ->set($this->validInput())
            ->call('send')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'reader@example.test', 'subject' => 'A question']);
        Mail::assertSent(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->hasTo('hello@example.test') && $mail->hasReplyTo('reader@example.test'));
    }

    public function test_validation_rejects_bad_input(): void
    {
        Livewire::test(Form::class)
            ->set(['name' => '', 'email' => 'nope', 'subject' => '', 'message' => 'short'])
            ->call('send')
            ->assertHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_filled_honeypot_is_silently_discarded(): void
    {
        Mail::fake();

        Livewire::test(Form::class)
            ->set($this->validInput())
            ->set('website', 'http://spam.test')
            ->call('send')
            ->assertSet('sent', true);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    public function test_sending_is_rate_limited(): void
    {
        Mail::fake();

        foreach (range(1, 5) as $attempt) {
            Livewire::test(Form::class)->set($this->validInput())->call('send')->assertHasNoErrors();
        }

        Livewire::test(Form::class)
            ->set($this->validInput())
            ->call('send')
            ->assertHasErrors('message');

        $this->assertDatabaseCount('contact_messages', 5);
    }

    public function test_mail_failure_does_not_lose_the_message(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        Livewire::test(Form::class)
            ->set($this->validInput())
            ->call('send')
            ->assertSet('sent', true);

        $this->assertSame(1, ContactMessage::count());
    }
}
