<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Cheap change tracking for Livewire polling.
 *
 * Every write to a tracked table stores a new random stamp in the cache. A polling component
 * compares the stamps of the tables it shows with the ones it last rendered, and only runs its
 * queries and re-renders when one has changed. An idle poll costs one cache read.
 */
class LiveUpdates
{
    private const PREFIX = 'live-updates:';

    public static function touch(string $topic): void
    {
        Cache::forever(self::PREFIX.$topic, Str::random(12));
    }

    /**
     * A single fingerprint for the given topics; it changes whenever any of them is touched.
     *
     * @param  list<string>  $topics
     */
    public static function stamp(array $topics): string
    {
        if ($topics === []) {
            return '';
        }

        $keys = array_map(fn (string $topic): string => self::PREFIX.$topic, $topics);

        return implode('|', array_map(fn (mixed $value): string => (string) $value, Cache::many($keys)));
    }
}
