<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['article_id', 'name', 'email', 'body', 'approved_at'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * @param  Builder<Comment>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->whereNotNull('approved_at');
    }

    /**
     * @param  Builder<Comment>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('approved_at');
    }
}
