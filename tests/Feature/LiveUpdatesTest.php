<?php

namespace Tests\Feature;

use App\Livewire\Admin\Articles\Index as ArticleIndex;
use App\Livewire\Admin\NavBadge;
use App\Livewire\Admin\Settings;
use App\Livewire\Articles\Browse;
use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Models\User;
use App\Support\LiveUpdates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class LiveUpdatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_writes_to_a_tracked_model_change_its_stamp(): void
    {
        $before = LiveUpdates::stamp(['articles']);

        $article = Article::factory()->create();
        $afterCreate = LiveUpdates::stamp(['articles']);

        $article->delete();

        $this->assertNotSame($before, $afterCreate);
        $this->assertNotSame($afterCreate, LiveUpdates::stamp(['articles']));
    }

    public function test_an_idle_poll_skips_rendering_and_runs_no_queries(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Article::factory()->create(['title' => 'Already listed']);

        $component = Livewire::test(ArticleIndex::class);

        DB::enableQueryLog();
        $component->call('checkForChanges')->assertSee('Already listed');

        $this->assertSame([], DB::getQueryLog());
    }

    public function test_admin_article_list_picks_up_new_articles_on_the_next_poll(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $component = Livewire::test(ArticleIndex::class)->assertDontSee('Written in another tab');

        Article::factory()->create(['title' => 'Written in another tab']);

        $component->call('checkForChanges')->assertSee('Written in another tab');
    }

    public function test_public_article_list_shows_newly_published_scheduled_articles(): void
    {
        $scheduled = Article::factory()->scheduled()->create(['title' => 'Due this minute']);

        $component = Livewire::test(Browse::class)->assertDontSee('Due this minute');

        $this->travelTo($scheduled->published_at->addMinute());
        $this->artisan('articles:publish-scheduled')->assertSuccessful();

        $component->call('checkForChanges')->assertSee('Due this minute');
    }

    public function test_settings_saved_elsewhere_are_flagged_without_overwriting_the_form(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Setting::set('site_name', 'Original name');

        $component = Livewire::test(Settings::class)
            ->set('site_name', 'Typing in progress')
            ->call('checkForChanges')
            ->assertSet('hasNewerSettings', false);

        Setting::set('site_name', 'Saved in another tab');

        $component->call('checkForChanges')
            ->assertSet('hasNewerSettings', true)
            ->assertSet('site_name', 'Typing in progress')
            ->assertSee('Load latest')
            ->call('loadSettings')
            ->assertSet('site_name', 'Saved in another tab')
            ->assertSet('hasNewerSettings', false);
    }

    public function test_saving_settings_does_not_flag_your_own_changes(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Setting::set('site_name', 'Name');
        Setting::set('contact_email', 'a@example.test');
        Setting::set('about_content', 'About');

        Livewire::test(Settings::class)
            ->set('tagline', 'A new tagline')
            ->call('save')
            ->assertHasNoErrors()
            ->call('checkForChanges')
            ->assertSet('hasNewerSettings', false);
    }

    public function test_sidebar_badge_updates_when_a_message_arrives(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $badge = Livewire::test(NavBadge::class, ['source' => 'messages'])->assertDontSee('1 new');

        ContactMessage::factory()->create();

        $badge->call('checkForChanges')->assertSee('1 new');

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('1 new');
    }
}
