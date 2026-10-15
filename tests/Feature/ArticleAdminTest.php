<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Livewire\Admin\Articles\Form;
use App\Livewire\Admin\Articles\Index;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->category = Category::factory()->create();
        $this->actingAs($this->admin);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function fillForm(array $overrides = []): Testable
    {
        $values = array_merge([
            'title' => 'A New Perspective',
            'excerpt' => 'A short summary.',
            'body' => "Some **markdown** body.\n\nSecond paragraph.",
            'category_id' => $this->category->id,
            'type' => 'opinion',
        ], $overrides);

        $component = Livewire::test(Form::class);

        foreach ($values as $key => $value) {
            $component->set($key, $value);
        }

        return $component;
    }

    public function test_slug_is_generated_from_title_and_editable(): void
    {
        $this->fillForm()->assertSet('slug', 'a-new-perspective')
            ->set('slug', 'Custom Slug!')
            ->assertSet('slug', 'custom-slug');
    }

    public function test_admin_can_save_a_draft(): void
    {
        $this->fillForm()->call('save')->assertHasNoErrors()->assertRedirect(route('admin.articles.index'));

        $article = Article::firstOrFail();
        $this->assertSame(ArticleStatus::Draft, $article->status);
        $this->assertNull($article->published_at);
        $this->assertSame(ArticleType::Opinion, $article->type);
        $this->assertSame($this->admin->id, $article->user_id);
        $this->assertSame(0, Article::published()->count());
    }

    public function test_admin_can_publish_immediately(): void
    {
        $this->fillForm(['status' => 'published', 'is_featured' => true])->call('save')->assertHasNoErrors();

        $article = Article::firstOrFail();
        $this->assertSame(1, Article::published()->count());
        $this->assertTrue($article->is_featured);
        $this->assertNotNull($article->published_at);
    }

    public function test_admin_can_schedule_for_the_future(): void
    {
        $date = now()->addDays(2)->format('Y-m-d\TH:i');

        $this->fillForm(['status' => 'scheduled', 'published_at' => $date])->call('save')->assertHasNoErrors();

        $article = Article::firstOrFail();
        $this->assertSame(ArticleStatus::Scheduled, $article->status);
        $this->assertTrue($article->published_at->isFuture());
        $this->assertSame(0, Article::published()->count());
    }

    public function test_scheduling_requires_a_future_date(): void
    {
        $this->fillForm(['status' => 'scheduled'])->call('save')->assertHasErrors('published_at');

        $this->fillForm(['status' => 'scheduled', 'published_at' => now()->subDay()->format('Y-m-d\TH:i')])
            ->call('save')
            ->assertHasErrors('published_at');

        $this->assertSame(0, Article::count());
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::test(Form::class)
            ->call('save')
            ->assertHasErrors(['title', 'excerpt', 'body']);
    }

    public function test_slug_must_be_unique(): void
    {
        Article::factory()->create(['slug' => 'taken']);

        $this->fillForm()->set('slug', 'taken')->call('save')->assertHasErrors('slug');
    }

    public function test_featured_image_is_stored_resized_and_needs_alt_text(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('cover.jpg', 2400, 1200);

        $this->fillForm()->set('image', $file)->call('save')->assertHasErrors('featured_image_alt');
        $this->assertSame(0, Article::count());

        $this->fillForm()
            ->set('image', UploadedFile::fake()->image('cover.jpg', 2400, 1200))
            ->set('featured_image_alt', 'A busy street at dusk')
            ->call('save')
            ->assertHasNoErrors();

        $article = Article::firstOrFail();
        Storage::disk('public')->assertExists($article->featured_image);
        $this->assertStringEndsWith('.webp', $article->featured_image);
        $this->assertSame('A busy street at dusk', $article->featured_image_alt);
        $this->assertLessThanOrEqual(1600, getimagesizefromstring(Storage::disk('public')->get($article->featured_image))[0]);
    }

    public function test_selected_images_can_be_removed_before_saving(): void
    {
        Storage::fake('public');

        $this->fillForm()
            ->set('image', UploadedFile::fake()->image('cover.jpg', 800, 400))
            ->call('clearImage')
            ->assertSet('image', null)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(Article::firstOrFail()->featured_image);

        $this->fillForm()
            ->set('bodyImage', UploadedFile::fake()->image('inline.jpg', 800, 400))
            ->set('bodyImageAlt', 'Alt')
            ->call('clearBodyImage')
            ->assertSet('bodyImage', null)
            ->assertSet('bodyImageAlt', '');
    }

    public function test_non_image_uploads_are_rejected(): void
    {
        Storage::fake('public');

        $this->fillForm()
            ->set('image', UploadedFile::fake()->create('script.php', 10, 'application/x-php'))
            ->assertHasErrors('image')
            ->assertSet('image', null);
    }

    public function test_admin_can_edit_and_replace_or_remove_the_image(): void
    {
        Storage::fake('public');
        $article = Article::factory()->for($this->category)->create(['slug' => 'original']);

        Livewire::test(Form::class, ['article' => $article])
            ->assertSet('title', $article->title)
            ->set('title', 'Edited title')
            ->set('image', UploadedFile::fake()->image('a.png', 800, 600))
            ->set('featured_image_alt', 'Alt')
            ->call('save')
            ->assertHasNoErrors();

        $article->refresh();
        $this->assertSame('Edited title', $article->title);
        $oldPath = $article->featured_image;
        Storage::disk('public')->assertExists($oldPath);

        Livewire::test(Form::class, ['article' => $article])
            ->set('removeImage', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($article->fresh()->featured_image);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_editing_keeps_own_slug_valid(): void
    {
        $article = Article::factory()->create(['slug' => 'mine']);

        Livewire::test(Form::class, ['article' => $article])->call('save')->assertHasNoErrors();
    }

    public function test_reading_time_updates_when_body_changes(): void
    {
        $this->fillForm(['body' => implode(' ', array_fill(0, 650, 'word'))])->call('save');

        $this->assertSame(4, Article::firstOrFail()->reading_time);
    }

    public function test_admin_can_delete_and_restore(): void
    {
        $article = Article::factory()->create();

        Livewire::test(Index::class)->call('delete', $article->id);
        $this->assertSoftDeleted($article);

        Livewire::test(Index::class)->set('trashed', true)->assertSee($article->title)->call('restore', $article->id);
        $this->assertNotSoftDeleted($article);
    }

    public function test_list_filters_by_search_status_and_category(): void
    {
        $match = Article::factory()->for($this->category)->create(['title' => 'Unique needle title']);
        $draft = Article::factory()->draft()->create(['title' => 'Other draft']);
        $other = Article::factory()->create(['title' => 'Elsewhere piece']);

        Livewire::test(Index::class)
            ->set('search', 'needle')->assertSee($match->title)->assertDontSee($other->title)
            ->set('search', '')
            ->set('status', 'draft')->assertSee($draft->title)->assertDontSee($match->title)
            ->set('status', '')
            ->set('category', (string) $this->category->id)->assertSee($match->title)->assertDontSee($other->title);
    }

    public function test_supporting_image_is_stored_and_markdown_is_dispatched_to_the_editor(): void
    {
        Storage::fake('public');

        Livewire::test(Form::class)
            ->set('bodyImage', UploadedFile::fake()->image('chart.png', 2000, 900))
            ->set('bodyImageAlt', 'A chart of growth')
            ->set('bodyImageCaption', 'Growth, "2020" to 2026')
            ->call('insertBodyImage')
            ->assertHasNoErrors()
            ->assertDispatched('markdown-insert', function (string $event, array $params): bool {
                return $params['editor'] === 'body'
                    && str_contains($params['markdown'], '![A chart of growth](/storage/articles/body/')
                    && str_contains($params['markdown'], ".webp \"Growth, '2020' to 2026\")");
            })
            ->assertSet('bodyImage', null)
            ->assertSet('bodyImageAlt', '');

        $this->assertCount(1, Storage::disk('public')->files('articles/body'));
    }

    public function test_supporting_image_needs_alt_text_and_a_file(): void
    {
        Storage::fake('public');

        Livewire::test(Form::class)
            ->set('bodyImage', UploadedFile::fake()->image('chart.png'))
            ->call('insertBodyImage')
            ->assertHasErrors('bodyImageAlt')
            ->assertNotDispatched('markdown-insert');

        Livewire::test(Form::class)
            ->set('bodyImageAlt', 'Alt')
            ->call('insertBodyImage')
            ->assertHasErrors('bodyImage');

        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_supporting_image_rejects_non_images(): void
    {
        Storage::fake('public');

        Livewire::test(Form::class)
            ->set('bodyImage', UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'))
            ->assertHasErrors('bodyImage')
            ->assertSet('bodyImage', null);
    }

    public function test_alt_text_cannot_break_out_of_the_markdown_image_syntax(): void
    {
        Storage::fake('public');

        Livewire::test(Form::class)
            ->set('bodyImage', UploadedFile::fake()->image('a.png'))
            ->set('bodyImageAlt', "Bad] (javascript:alert(1)\n[x")
            ->call('insertBodyImage')
            ->assertDispatched('markdown-insert', function (string $event, array $params): bool {
                return str_starts_with($params['markdown'], "\n\n![Bad  (javascript:alert(1)  x](/storage/");
            });
    }

    public function test_non_admin_cannot_mutate_via_components(): void
    {
        $article = Article::factory()->create();
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)->assertForbidden();
        $this->assertNotSoftDeleted($article);
    }
}
