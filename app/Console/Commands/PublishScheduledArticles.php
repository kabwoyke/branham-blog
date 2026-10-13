<?php

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('articles:publish-scheduled')]
#[Description('Publish scheduled articles whose publish date has passed')]
class PublishScheduledArticles extends Command
{
    public function handle(): int
    {
        $count = Article::query()
            ->where('status', ArticleStatus::Scheduled)
            ->where('published_at', '<=', now())
            ->update(['status' => ArticleStatus::Published]);

        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
