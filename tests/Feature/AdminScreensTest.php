<?php

namespace Tests\Feature;

use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Messages\Index as MessageIndex;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Subscribers\Index as SubscriberIndex;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminScreensTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_category_can_be_edited_but_keeps_its_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'fixed-slug']);

        Livewire::test(CategoryIndex::class)
            ->call('edit', $category->id)
            ->assertSet('name', $category->name)
            ->set('name', 'Renamed')
            ->set('description', 'New description')
            ->call('save')
            ->assertHasNoErrors();

        $category->refresh();
        $this->assertSame('Renamed', $category->name);
        $this->assertSame('fixed-slug', $category->slug);
    }

    public function test_category_name_is_required(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CategoryIndex::class)->call('edit', $category->id)->set('name', '')->call('save')->assertHasErrors('name');
    }

    public function test_subscribers_can_be_searched_and_removed(): void
    {
        $keep = Subscriber::factory()->create(['email' => 'keep@example.test']);
        $remove = Subscriber::factory()->create(['email' => 'remove@example.test']);

        Livewire::test(SubscriberIndex::class)
            ->set('search', 'remove')->assertSee('remove@example.test')->assertDontSee('keep@example.test')
            ->call('delete', $remove->id);

        $this->assertModelMissing($remove);
        $this->assertModelExists($keep);
    }

    public function test_subscribers_export_as_csv(): void
    {
        Subscriber::factory()->create(['email' => 'one@example.test']);
        Subscriber::factory()->unconfirmed()->create(['email' => '=cmd@example.test']);

        $response = $this->get(route('admin.subscribers.export'))->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();
        $this->assertStringContainsString('email,status,subscribed_at,confirmed_at', $csv);
        $this->assertStringContainsString('one@example.test,confirmed', $csv);
        $this->assertStringContainsString("'=cmd@example.test,pending", $csv);
    }

    public function test_opening_a_message_marks_it_read_and_it_can_be_unmarked(): void
    {
        $message = ContactMessage::factory()->create();

        Livewire::test(MessageIndex::class)
            ->call('open', $message->id)
            ->assertSet('openId', $message->id)
            ->assertSee($message->message);

        $this->assertTrue($message->fresh()->isRead());

        Livewire::test(MessageIndex::class)->call('markUnread', $message->id);
        $this->assertFalse($message->fresh()->isRead());
    }

    public function test_messages_can_be_filtered_to_unread_and_deleted(): void
    {
        $read = ContactMessage::factory()->read()->create(['subject' => 'Already handled']);
        $unread = ContactMessage::factory()->create(['subject' => 'Needs reply']);

        Livewire::test(MessageIndex::class)
            ->set('unreadOnly', true)->assertSee('Needs reply')->assertDontSee('Already handled')
            ->call('delete', $unread->id);

        $this->assertModelMissing($unread);
        $this->assertModelExists($read);
    }

    public function test_settings_are_loaded_and_saved(): void
    {
        Setting::set('site_name', 'Old name');
        Setting::set('contact_email', 'old@example.test');
        Setting::set('about_content', 'Old about');

        Livewire::test(Settings::class)
            ->assertSet('site_name', 'Old name')
            ->set('site_name', 'New name')
            ->set('tagline', 'A tagline')
            ->set('linkedin_url', 'https://www.linkedin.com/in/example')
            ->set('about_content', 'New **about**')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New name', Setting::get('site_name'));
        $this->assertSame('New **about**', Setting::get('about_content'));
        $this->assertSame('https://www.linkedin.com/in/example', Setting::get('linkedin_url'));
    }

    public function test_settings_validate_email_and_linkedin_url(): void
    {
        Livewire::test(Settings::class)
            ->set('site_name', 'Name')
            ->set('about_content', 'About')
            ->set('contact_email', 'not-an-email')
            ->set('linkedin_url', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['contact_email', 'linkedin_url']);
    }
}
