<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    /**
     * Render Markdown to HTML with raw HTML stripped and unsafe links blocked.
     *
     * Images are lazy-loaded, and an image with a title ("![alt](url "Caption")") becomes a captioned figure.
     */
    public static function toHtml(?string $markdown): string
    {
        $html = Str::markdown($markdown ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return self::decorateImages($html);
    }

    private static function decorateImages(string $html): string
    {
        $html = preg_replace_callback(
            '#<p>\s*(<img [^>]*?>)\s*</p>#i',
            function (array $match): string {
                $image = $match[1];

                if (preg_match('/ title="([^"]*)"/', $image, $title) !== 1 || $title[1] === '') {
                    return $match[0];
                }

                $image = str_replace($title[0], '', $image);

                return "<figure>{$image}<figcaption>{$title[1]}</figcaption></figure>";
            },
            $html,
        ) ?? $html;

        return preg_replace('/<img (?![^>]*\bloading=)/i', '<img loading="lazy" ', $html) ?? $html;
    }
}
