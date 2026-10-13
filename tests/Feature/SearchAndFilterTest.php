<?php

namespace Tests\Feature;

use App\Enums\ArticleType;
use App\Livewire\Articles\Browse;
use App\Models\Article;
use App\Models\Category;
use App\Support\ArticleSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Attributes\Url;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use ReflectionProperty;
use Tests\TestCase;

class SearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_title_excerpt_and_body(): void
    {
        Article::factory()->create(['title' => 'Zebra in the title', 'excerpt' => 'x', 'body' => 'x']);
        Article::factory()->create(['title' => 'Plain one', 'excerpt' => 'A zebra in the excerpt', 'body' => 'x']);
        Article::factory()->create(['title' => 'Plain two', 'excerpt' => 'x', 'body' => 'Deep in the body, a zebra.']);
        Article::factory()->create(['title' => 'Unrelated', 'excerpt' => 'x', 'body' => 'x']);

        Livewire::test(Browse::class)
            ->set('search', 'zebra')
            ->assertSee('Zebra in the title')
            ->assertSee('Plain one')
            ->assertSee('Plain two')
            ->assertDontSee('Unrelated')
            ->assertSee('3 articles matching');
    }

    public function test_search_is_case_insensitive_and_requires_every_word(): void
    {
        Article::factory()->create(['title' => 'Trust in institutions', 'excerpt' => 'x', 'body' => 'x']);
        Article::factory()->create(['title' => 'Trust and money', 'excerpt' => 'x', 'body' => 'x']);

        Livewire::test(Browse::class)
            ->set('search', 'TRUST institutions')
            ->assertSee('Trust in institutions')
            ->assertDontSee('Trust and money');
    }

    public function test_search_never_returns_unpublished_articles(): void
    {
        Article::factory()->create(['title' => 'Public quokka']);
        Article::factory()->draft()->create(['title' => 'Draft quokka']);
        Article::factory()->scheduled()->create(['title' => 'Scheduled quokka']);

        Livewire::test(Browse::class)
            ->set('search', 'quokka')
            ->assertSee('Public quokka')
            ->assertDontSee('Draft quokka')
            ->assertDontSee('Scheduled quokka');
    }

    public function test_like_wildcards_typed_by_visitors_are_treated_literally(): void
    {
        Article::factory()->create(['title' => 'Ordinary article', 'excerpt' => 'x', 'body' => 'x']);
        Article::factory()->create(['title' => 'Growth hit 50% this year', 'excerpt' => 'x', 'body' => 'x']);

        Livewire::test(Browse::class)
            ->set('search', '%')
            ->assertSee('Growth hit 50% this year')
            ->assertDontSee('Ordinary article');

        Livewire::test(Browse::class)
            ->set('search', '_')
            ->assertDontSee('Ordinary article');
    }

    public function test_search_input_is_bounded(): void
    {
        $this->assertCount(ArticleSearch::MAX_WORDS, ArticleSearch::words('a b c d e f g h'));
        $this->assertSame(ArticleSearch::MAX_TERM_LENGTH, mb_strlen(ArticleSearch::words(str_repeat('a', 500))[0]));
    }

    public function test_empty_state_offers_to_clear_filters(): void
    {
        Article::factory()->create();

        Livewire::test(Browse::class)
            ->set('search', 'nothing-matches-this')
            ->assertSee('No articles found')
            ->assertSee('Clear search and filters')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertDontSee('No articles found');
    }

    public function test_empty_site_shows_a_gentle_empty_state_without_clear_button(): void
    {
        Livewire::test(Browse::class)
            ->assertSee('No articles found')
            ->assertDontSee('Clear search and filters');
    }

    public function test_type_filter(): void
    {
        Article::factory()->ofType(ArticleType::Research)->create(['title' => 'A research piece']);
        Article::factory()->ofType(ArticleType::Opinion)->create(['title' => 'An opinion piece']);

        Livewire::test(Browse::class)
            ->set('type', 'research')
            ->assertSee('A research piece')
            ->assertDontSee('An opinion piece')
            ->set('type', 'not-a-type')
            ->assertSee('A research piece')
            ->assertSee('An opinion piece');
    }

    public function test_category_route_filters_and_combines_with_search_and_type(): void
    {
        $category = Category::factory()->create(['slug' => 'trust', 'name' => 'Trust']);
        Article::factory()->for($category)->ofType(ArticleType::Opinion)->create(['title' => 'Trust opinion owl']);
        Article::factory()->for($category)->ofType(ArticleType::Research)->create(['title' => 'Trust research owl']);
        Article::factory()->for($category)->create(['title' => 'Trust other', 'excerpt' => 'x', 'body' => 'x']);
        Article::factory()->create(['title' => 'Elsewhere owl']);

        $this->get(route('categories.show', 'trust'))
            ->assertOk()
            ->assertSee('Trust opinion owl')
            ->assertDontSee('Elsewhere owl');

        $this->get(route('categories.show', ['category' => 'trust', 'q' => 'owl', 'type' => 'research']))
            ->assertOk()
            ->assertSee('Trust research owl')
            ->assertDontSee('Trust opinion owl')
            ->assertDontSee('Trust other')
            ->assertDontSee('Elsewhere owl');
    }

    public function test_filters_are_read_from_the_url_for_shareable_links(): void
    {
        Article::factory()->create(['title' => 'Findable heron']);
        Article::factory()->create(['title' => 'Other thing', 'excerpt' => 'x', 'body' => 'x']);

        $this->get(route('articles.index', ['q' => 'heron']))
            ->assertOk()
            ->assertSee('Findable heron')
            ->assertDontSee('Other thing');

        Livewire::withQueryParams(['q' => 'heron', 'type' => 'blog'])
            ->test(Browse::class)
            ->assertSet('search', 'heron')
            ->assertSet('type', 'blog');
    }

    public function test_search_state_is_bound_to_the_query_string(): void
    {
        foreach (['search', 'type'] as $property) {
            $attributes = (new ReflectionProperty(Browse::class, $property))->getAttributes(Url::class);

            $this->assertCount(1, $attributes, "{$property} should be bound to the URL");
        }
    }

    public function test_changing_filters_resets_to_the_first_page(): void
    {
        Article::factory()->count(12)->create(['title' => 'Searchable ibis']);

        Livewire::test(Browse::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'ibis')
            ->assertSet('paginators.page', 1);
    }

    public function test_pagination_keeps_the_search_and_type_filters(): void
    {
        Article::factory()->count(12)->ofType(ArticleType::Blog)->sequence(fn ($sequence) => [
            'title' => 'Migratory crane '.$sequence->index,
            'published_at' => now()->subDays(30 - $sequence->index),
        ])->create();
        Article::factory()->ofType(ArticleType::Opinion)->create(['title' => 'Migratory crane opinion']);

        Livewire::test(Browse::class)
            ->set('search', 'crane')
            ->set('type', 'blog')
            ->assertSee('12 articles matching')
            ->assertSee('Migratory crane 11')
            ->assertDontSee('Migratory crane 0<', false)
            ->assertDontSee('Migratory crane opinion')
            ->call('nextPage')
            ->assertSee('Migratory crane 0')
            ->assertDontSee('Migratory crane 11')
            ->assertSet('search', 'crane')
            ->assertSet('type', 'blog');
    }

    public function test_pagination_links_work_without_javascript_and_keep_filters(): void
    {
        $category = Category::factory()->create(['slug' => 'trust']);
        Article::factory()->count(10)->for($category)->create(['title' => 'Pelican notes']);

        $this->get(route('categories.show', ['category' => 'trust', 'q' => 'pelican', 'type' => 'blog']))->assertOk();

        $this->get(route('categories.show', ['category' => 'trust', 'q' => 'pelican']))
            ->assertSee(e(route('categories.show', ['category' => 'trust', 'q' => 'pelican', 'page' => 2])), false);

        $this->get(route('articles.index', ['q' => 'pelican']))
            ->assertSee(e(route('articles.index', ['q' => 'pelican', 'page' => 2])), false);
    }

    public function test_category_cannot_be_changed_from_the_browser(): void
    {
        $category = Category::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(Browse::class, ['category' => $category])->set('category', null);
    }

    public function test_unknown_category_slug_is_a_404(): void
    {
        $this->get(route('categories.show', 'missing'))->assertNotFound();
    }

    public function test_featured_page_lists_only_published_featured_articles(): void
    {
        Article::factory()->featured()->create(['title' => 'Star piece']);
        Article::factory()->create(['title' => 'Regular piece']);
        Article::factory()->featured()->draft()->create(['title' => 'Featured draft']);
        Article::factory()->featured()->scheduled()->create(['title' => 'Featured scheduled']);

        $this->get(route('articles.featured'))
            ->assertOk()
            ->assertSee('Featured articles')
            ->assertSee('Star piece')
            ->assertDontSee('Regular piece')
            ->assertDontSee('Featured draft')
            ->assertDontSee('Featured scheduled');
    }

    public function test_featured_page_has_an_empty_state(): void
    {
        $this->get(route('articles.featured'))->assertOk()->assertSee('No featured articles yet');
    }

    public function test_header_has_search_and_featured_links(): void
    {
        $this->get(route('home'))
            ->assertSee('name="q"', false)
            ->assertSee(route('articles.featured'), false);
    }
}
