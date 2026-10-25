<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

#[Fillable(['article_id', 'viewed_on', 'views'])]
class ArticleView extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewed_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Count one view of a public article. Bots, the admin and repeat views in the same session are ignored.
     */
    public static function record(Article $article, Request $request): void
    {
        $sessionKey = "viewed.{$article->id}";

        if ($request->user() !== null || $request->isMethod('HEAD') || self::isBot((string) $request->userAgent())) {
            return;
        }

        if ($request->session()->has($sessionKey)) {
            return;
        }

        $request->session()->put($sessionKey, true);

        self::firstOrCreate(
            ['article_id' => $article->id, 'viewed_on' => today()->toDateString()],
            ['views' => 0],
        )->increment('views');
    }

    private static function isBot(string $userAgent): bool
    {
        return $userAgent === '' || preg_match('/bot|crawl|spider|slurp|preview|fetch|curl|wget|headless/i', $userAgent) === 1;
    }
}
