<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Subscriber;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberAndMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscriber_gets_an_unsubscribe_token_on_creation(): void
    {
        $subscriber = Subscriber::create(['email' => 'reader@example.test']);

        $this->assertSame(64, strlen($subscriber->unsubscribe_token));
        $this->assertFalse($subscriber->isConfirmed());
    }

    public function test_confirmed_scope(): void
    {
        Subscriber::factory()->unconfirmed()->create();
        $confirmed = Subscriber::factory()->create();

        $this->assertSame([$confirmed->id], Subscriber::confirmed()->pluck('id')->all());
    }

    public function test_subscriber_emails_are_unique(): void
    {
        Subscriber::factory()->create(['email' => 'same@example.test']);

        $this->expectException(QueryException::class);

        Subscriber::factory()->create(['email' => 'same@example.test']);
    }

    public function test_contact_message_unread_scope(): void
    {
        ContactMessage::factory()->read()->create();
        $unread = ContactMessage::factory()->create();

        $this->assertTrue(ContactMessage::factory()->read()->create()->isRead());
        $this->assertSame([$unread->id], ContactMessage::unread()->pluck('id')->all());
    }
}
