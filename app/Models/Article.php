<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Support\ImageStore;
use App\Support\Markdown;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id', 'category_id', 'title', 'slug', 'excerpt', 'body', 'type', 'featured_image',
    'featured_image_alt', 'is_featured', 'status', 'published_at', 'reading_time',
    'meta_title', 'meta_description',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, SoftDeletes;

    private const WORDS_PER_MINUTE = 200;

    protected static function booted(): void
    {
        $forgetSitemap = fn () => Cache::forget('sitemap-xml');
        static::saved($forgetSitemap);
        static::deleted($forgetSitemap);
        static::restored($forgetSitemap);

        static::saving(function (Article $article): void {
            if ($article->isDirty('body')) {
                $article->reading_time = self::calculateReadingTime($article->body);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ArticleType::class,
            'status' => ArticleStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'newsletter_sent_at' => 'datetime',
        ];
    }

    /**
     * The body rendered from Markdown to safe HTML.
     */
    protected function bodyHtml(): Attribute
    {
        return Attribute::get(fn (): string => Markdown::toHtml($this->body));
    }

    /**
     * Public URL of the featured image, if one is set.
     */
    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->featured_image ? Storage::disk(ImageStore::DISK)->url($this->featured_image) : null
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Estimate minutes needed to read the given body, never less than one.
     */
    public static function calculateReadingTime(string $body): int
    {
        $wordCount = str_word_count(strip_tags($body));

        return max(1, (int) ceil($wordCount / self::WORDS_PER_MINUTE));
    }

    /**
     * The next older published article, if any.
     */
    public function previousArticle(): ?Article
    {
        return static::published()
            ->where('published_at', '<', $this->published_at)
            ->latest('published_at')
            ->first(['id', 'slug', 'title']);
    }

    /**
     * The next newer published article, if any.
     */
    public function nextArticle(): ?Article
    {
        return static::published()
            ->where('published_at', '>', $this->published_at)
            ->oldest('published_at')
            ->first(['id', 'slug', 'title']);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<ArticleView, $this>
     */
    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Whether guests can currently see this article.
     */
    public function isPublic(): bool
    {
        return $this->status === ArticleStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /**
     * Published articles from the same category first, then the most recent others.
     *
     * @return Collection<int, Article>
     */
    public function related(int $limit = 3): Collection
    {
        $base = fn () => static::published()->with(['category', 'author'])->whereKeyNot($this->getKey())->latest('published_at');

        $related = $base()->where('category_id', $this->category_id)->limit($limit)->get();

        if ($related->count() < $limit) {
            $related = $related->concat(
                $base()->where('category_id', '!=', $this->category_id)->limit($limit - $related->count())->get()
            );
        }

        return $related;
    }

    /**
     * Articles visible to the public: published and not future-dated.
     *
     * @param  Builder<Article>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Article>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Limit to a category given as a model, an id or a slug.
     *
     * @param  Builder<Article>  $query
     */
    public function scopeInCategory(Builder $query, Category|int|string $category): void
    {
        if ($category instanceof Category) {
            $query->where('category_id', $category->getKey());
        } elseif (is_int($category)) {
            $query->where('category_id', $category);
        } else {
            $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $category));
        }
    }
}
