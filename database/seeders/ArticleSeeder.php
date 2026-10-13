<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sample articles for local development only. Bodies are Markdown.
 */
class ArticleSeeder extends Seeder
{
    /**
     * @var list<array{title: string, category: string, type: ArticleType, excerpt: string, featured?: bool}>
     */
    private const ARTICLES = [
        ['title' => 'Why people queue less and scroll more', 'category' => 'society-and-behaviour', 'type' => ArticleType::Blog, 'excerpt' => 'Waiting has changed shape. What does the disappearing queue tell us about patience and expectation?', 'featured' => true],
        ['title' => 'The quiet return of the local shop', 'category' => 'business-and-strategy', 'type' => ArticleType::Opinion, 'excerpt' => 'After a decade of scale, small neighbourhood businesses are finding a new advantage: being known.'],
        ['title' => 'What Gen Z actually means by work-life balance', 'category' => 'generations-and-lifestyles', 'type' => ArticleType::Research, 'excerpt' => 'The phrase is the same across generations. The meaning is not.', 'featured' => true],
        ['title' => 'Trust is slower than technology', 'category' => 'institutions-and-trust', 'type' => ArticleType::Opinion, 'excerpt' => 'Institutions can adopt new tools overnight. Earning belief still takes years.'],
        ['title' => 'A note on meetings that could have been a paragraph', 'category' => 'notes-and-observations', 'type' => ArticleType::Blog, 'excerpt' => 'A short observation about how organisations spend attention.'],
        ['title' => 'How to read a market signal without overreading it', 'category' => 'business-and-strategy', 'type' => ArticleType::Educational, 'excerpt' => 'A practical framework for separating early patterns from noise.'],
        ['title' => 'The generational handover of household finance', 'category' => 'generations-and-lifestyles', 'type' => ArticleType::Research, 'excerpt' => 'Wealth is moving between generations, and so are habits about money.'],
        ['title' => 'When public services go digital, who is left out?', 'category' => 'institutions-and-trust', 'type' => ArticleType::Research, 'excerpt' => 'Efficiency gains are real. So are the quiet exclusions that come with them.'],
        ['title' => 'Notes from a commute', 'category' => 'notes-and-observations', 'type' => ArticleType::Blog, 'excerpt' => 'Small things noticed between home and work, and what they might add up to.'],
        ['title' => 'Understanding behavioural nudges in policy', 'category' => 'society-and-behaviour', 'type' => ArticleType::Educational, 'excerpt' => 'What nudges are, where they work, and where they quietly fail.'],
        ['title' => 'Strategy is mostly about what you refuse to do', 'category' => 'business-and-strategy', 'type' => ArticleType::Opinion, 'excerpt' => 'Good strategy narrows choices. Most plans widen them.'],
        ['title' => 'Loneliness as an economic question', 'category' => 'society-and-behaviour', 'type' => ArticleType::Research, 'excerpt' => 'Social isolation is usually framed as personal. Its costs are collective.'],
        ['title' => 'Three futures for the four-day week', 'category' => 'generations-and-lifestyles', 'type' => ArticleType::Opinion, 'excerpt' => 'Pilots are promising, but the long-run outcome is far from settled.'],
        ['title' => 'Why institutions should explain, not just announce', 'category' => 'institutions-and-trust', 'type' => ArticleType::Blog, 'excerpt' => 'Transparency is not the same as communication. People notice the difference.'],
    ];

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create();
        $categories = Category::query()->pluck('id', 'slug');

        foreach (self::ARTICLES as $index => $data) {
            $slug = Str::slug($data['title']);
            $hasImage = $index % 5 !== 4;
            $inlineUrl = Storage::disk('public')->url(SampleImageGenerator::store("{$slug}-inline", 1000, 560));

            Article::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $author->id,
                    'category_id' => $categories[$data['category']],
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'body' => $this->body($data['title'], $data['excerpt'], $inlineUrl),
                    'featured_image' => $hasImage ? SampleImageGenerator::store($slug) : null,
                    'featured_image_alt' => $hasImage ? 'Abstract artwork in green and ivory tones (sample image)' : null,
                    'type' => $data['type'],
                    'is_featured' => $data['featured'] ?? false,
                    'status' => ArticleStatus::Published,
                    'published_at' => now()->subDays(count(self::ARTICLES) - $index)->setTime(9, 0),
                ],
            );
        }

        $categoryId = $categories->first();

        $this->createOnce('draft-ideas-on-remote-leadership', Article::factory()->draft()->raw([
            'user_id' => $author->id,
            'category_id' => $categoryId,
            'title' => 'Draft: ideas on remote leadership',
            'slug' => 'draft-ideas-on-remote-leadership',
        ]));

        $this->createOnce('scheduled-the-next-decade-of-retail', Article::factory()->scheduled()->raw([
            'user_id' => $author->id,
            'category_id' => $categoryId,
            'title' => 'Scheduled: the next decade of retail',
            'slug' => 'scheduled-the-next-decade-of-retail',
        ]));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOnce(string $slug, array $attributes): void
    {
        Article::firstOrCreate(['slug' => $slug], $attributes);
    }

    private function body(string $title, string $excerpt, string $inlineUrl): string
    {
        return <<<MD
        {$excerpt}

        ## The observation

        Sample content for development. It stands in for a real article so layouts, pagination and reading time can be checked while building. Replace it by writing real articles in the admin area.

        Good analysis starts by noticing something ordinary, then asking what it implies. "{$title}" is a placeholder for that process.

        ## What it could mean

        Patterns rarely announce themselves. They appear as small changes in behaviour, then in markets, then in policy. The value of anticipating them is not certainty, but preparedness.

        ![Abstract artwork used as a sample supporting image]({$inlineUrl} "A sample supporting image with a caption")

        > The future is largely unpredictable, but there is real value in thinking it through.

        ## A closing thought

        Observations are a starting point. The conversation they open matters more than the conclusion they suggest.
        MD;
    }
}
