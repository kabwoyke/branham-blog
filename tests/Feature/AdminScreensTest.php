<?php

namespace Tests\Feature;

use App\Livewire\Admin\Categories\Index as CategoryIndex;
use App\Livewire\Admin\Messages\Index as MessageIndex;
use App\Livewire\Admin\Password;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Subscribers\Index as SubscriberIndex;
use App\Models\Article;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            ->assertHasNoErrors()
            ->assertDispatched('notify', type: 'status', message: 'Settings saved.');

        $this->assertSame('New name', Setting::get('site_name'));
        $this->get(route('about'))->assertSee('<strong>about</strong>', false);
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

    public function test_social_media_profiles_are_saved_and_must_match_their_platform(): void
    {
        Setting::set('site_name', 'Name');
        Setting::set('contact_email', 'a@example.test');
        Setting::set('about_content', 'About');

        Livewire::test(Settings::class)
            ->set('x_url', 'https://www.instagram.com/branham')
            ->set('instagram_url', 'http://instagram.com/branham')
            ->set('tiktok_url', 'https://evil-tiktok.com/@branham')
            ->call('save')
            ->assertHasErrors(['x_url', 'instagram_url', 'tiktok_url'])
            ->set('x_url', 'https://twitter.com/branham')
            ->set('instagram_url', 'https://www.instagram.com/branham')
            ->set('tiktok_url', 'https://www.tiktok.com/@branham')
            ->set('facebook_url', ' https://www.facebook.com/branham ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('https://twitter.com/branham', Setting::get('x_url'));
        $this->assertSame('https://www.facebook.com/branham', Setting::get('facebook_url'));
    }

    public function test_password_can_be_changed_with_the_current_password(): void
    {
        $user = User::factory()->admin()->create(['password' => 'old-password-123']);
        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'old-password-123')
            ->set('password', 'brand-new-password')
            ->set('password_confirmation', 'brand-new-password')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->admin()->create(['password' => 'old-password-123']);
        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'wrong')
            ->set('password', 'brand-new-password')
            ->set('password_confirmation', 'brand-new-password')
            ->call('save')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_category_can_be_created_with_a_unique_slug(): void
    {
        Category::factory()->create(['name' => 'Future Trends', 'slug' => 'future-trends']);

        Livewire::test(CategoryIndex::class)
            ->call('create')
            ->set('name', 'Future Trends')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'future-trends-2']);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CategoryIndex::class)->call('delete', $category->id);

        $this->assertModelMissing($category);
    }

    public function test_category_with_articles_cannot_be_deleted(): void
    {
        $article = Article::factory()->create();

        Livewire::test(CategoryIndex::class)->call('delete', $article->category_id);

        $this->assertModelExists($article->category);
    }
}
