<?php

namespace App\Models\Concerns;

use App\Support\LiveUpdates;
use Illuminate\Database\Eloquent\Model;

/**
 * Marks the model's table as changed whenever a row is written, so polling Livewire screens refresh.
 *
 * The topic is the table name (e.g. "articles").
 */
trait TracksLiveUpdates
{
    public static function bootTracksLiveUpdates(): void
    {
        $touch = fn (Model $model) => LiveUpdates::touch($model->getTable());

        static::saved($touch);
        static::deleted($touch);

        if (method_exists(static::class, 'restored')) {
            static::restored($touch);
        }
    }
}
