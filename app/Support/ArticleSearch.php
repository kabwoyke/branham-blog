<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;

/**
 * Keyword search over article titles, excerpts and bodies using SQL LIKE.
 *
 * This class is the single place that decides how articles are searched. To move to Laravel Scout
 * later, replace the body of apply() (for example with Article::search($term)->keys()) and keep the signature.
 */
class ArticleSearch
{
    public const MAX_TERM_LENGTH = 100;

    public const MAX_WORDS = 5;

    private const ESCAPE = '!';

    /**
     * Narrow the query to articles where every word appears in the title, excerpt or body.
     *
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    public static function apply(Builder $query, string $term): Builder
    {
        $grammar = $query->getQuery()->getGrammar();
        $columns = array_map(
            fn (string $column): string => $grammar->wrap($query->getModel()->qualifyColumn($column)),
            ['title', 'excerpt', 'body'],
        );
        $condition = implode(' or ', array_map(fn (string $column): string => $column.' like ? escape \''.self::ESCAPE.'\'', $columns));

        foreach (self::words($term) as $word) {
            $pattern = '%'.self::escapeLike($word).'%';

            $query->whereRaw("({$condition})", [$pattern, $pattern, $pattern]);
        }

        return $query;
    }

    /**
     * @return list<string>
     */
    public static function words(string $term): array
    {
        $term = mb_substr(trim($term), 0, self::MAX_TERM_LENGTH);
        $words = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_slice($words, 0, self::MAX_WORDS);
    }

    /**
     * Stop user-typed % and _ from acting as wildcards.
     */
    private static function escapeLike(string $value): string
    {
        return str_replace([self::ESCAPE, '%', '_'], [self::ESCAPE.self::ESCAPE, self::ESCAPE.'%', self::ESCAPE.'_'], $value);
    }
}
