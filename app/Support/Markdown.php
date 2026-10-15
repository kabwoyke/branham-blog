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

        return self::decorateImages(self::addHeadingIds($html));
    }

    /**
     * The h2 and h3 headings of rendered HTML, for a table of contents.
     *
     * @return list<array{id: string, text: string, level: int}>
     */
    public static function headings(string $html): array
    {
        preg_match_all('#<h([23]) id="([^"]+)">(.*?)</h\1>#is', $html, $matches, PREG_SET_ORDER);

        return array_map(fn (array $match): array => [
            'id' => $match[2],
            'text' => trim(html_entity_decode(strip_tags($match[3]))),
            'level' => (int) $match[1],
        ], $matches);
    }

    private static function addHeadingIds(string $html): string
    {
        $used = [];

        return preg_replace_callback('#<h([23])>(.*?)</h\1>#is', function (array $match) use (&$used): string {
            $base = Str::slug(html_entity_decode(strip_tags($match[2]))) ?: 'section';
            $id = $base;

            for ($suffix = 2; in_array($id, $used, true); $suffix++) {
                $id = "{$base}-{$suffix}";
            }

            $used[] = $id;

            return "<h{$match[1]} id=\"{$id}\">{$match[2]}</h{$match[1]}>";
        }, $html) ?? $html;
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
