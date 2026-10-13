<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    /**
     * Render Markdown to HTML with raw HTML stripped and unsafe links blocked.
     */
    public static function toHtml(?string $markdown): string
    {
        return Str::markdown($markdown ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
