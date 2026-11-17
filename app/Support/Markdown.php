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

    /**
     * The h2 and h3 sections of a Markdown document, keyed the same way as the rendered heading ids.
     *
     * Headings inside fenced code blocks are ignored.
     *
     * @return list<array{key: string, text: string, level: int, line: int}>
     */
    public static function sections(?string $markdown): array
    {
        $sections = [];
        $used = [];
        $fence = null;

        foreach (preg_split('/\R/', $markdown ?? '') ?: [] as $number => $line) {
            if (preg_match('/^\s{0,3}(`{3,}|~{3,})/', $line, $marker) === 1) {
                if ($fence === null) {
                    $fence = $marker[1][0];
                } elseif ($marker[1][0] === $fence) {
                    $fence = null;
                }

                continue;
            }

            if ($fence !== null || preg_match('/^\s{0,3}(#{2,3})\s+(.+?)(?:\s+#+)?\s*$/', $line, $match) !== 1) {
                continue;
            }

            $text = trim($match[2]);
            $base = Str::slug(html_entity_decode(strip_tags(Str::markdown($text)))) ?: 'section';
            $key = $base;

            for ($suffix = 2; in_array($key, $used, true); $suffix++) {
                $key = "{$base}-{$suffix}";
            }

            $used[] = $key;
            $sections[] = ['key' => $key, 'text' => $text, 'level' => strlen($match[1]), 'line' => $number];
        }

        return $sections;
    }

    /**
     * Insert a Markdown snippet at a placement: "start", "end", "below:{key}" (just under that heading)
     * or "section-end:{key}" (after the last paragraph of that section, before the next heading of the same or higher level).
     */
    public static function insertAt(string $markdown, string $placement, string $snippet): string
    {
        $snippet = trim($snippet);
        $lines = preg_split('/\R/', $markdown) ?: [];

        [$where, $key] = array_pad(explode(':', $placement, 2), 2, null);
        $sections = self::sections($markdown);
        $index = array_search($key, array_column($sections, 'key'), true);

        $splitAt = match (true) {
            $where === 'start' => 0,
            $where === 'below' && $index !== false => $sections[$index]['line'] + 1,
            $where === 'section-end' && $index !== false => self::sectionEndLine($sections, $index, count($lines)),
            default => count($lines),
        };

        $before = rtrim(implode("\n", array_slice($lines, 0, $splitAt)));
        $after = ltrim(implode("\n", array_slice($lines, $splitAt)), "\r\n");

        return implode("\n\n", array_filter([$before, $snippet, $after], fn (string $part): bool => $part !== ''))."\n";
    }

    /**
     * @param  list<array{key: string, text: string, level: int, line: int}>  $sections
     */
    private static function sectionEndLine(array $sections, int $index, int $lineCount): int
    {
        for ($next = $index + 1; $next < count($sections); $next++) {
            if ($sections[$next]['level'] <= $sections[$index]['level']) {
                return $sections[$next]['line'];
            }
        }

        return $lineCount;
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
