<?php

namespace Database\Seeders;

use GdImage;
use Illuminate\Support\Facades\Storage;

/**
 * Creates abstract placeholder artwork for development sample articles. Not used in production.
 */
class SampleImageGenerator
{
    /**
     * Gradient start and end colours, in the site's calm green, slate and ivory range.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const PALETTES = [
        ['#2F5D50', '#8FB7A5'],
        ['#24282D', '#66716D'],
        ['#B9A583', '#FAF7F2'],
        ['#3E7A68', '#D8E6DF'],
        ['#4F6482', '#C7D3E3'],
        ['#9C6149', '#F0D9C8'],
    ];

    /**
     * Store a deterministic image for the given key and return its path on the public disk.
     */
    public static function store(string $key, int $width = 1200, int $height = 675): string
    {
        $path = "articles/sample/{$key}.webp";
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk->put($path, self::render($key, $width, $height));
        }

        return $path;
    }

    private static function render(string $key, int $width, int $height): string
    {
        mt_srand(crc32($key));

        $image = imagecreatetruecolor($width, $height);
        [$from, $to] = self::PALETTES[mt_rand(0, count(self::PALETTES) - 1)];

        self::gradient($image, $width, $height, self::rgb($from), self::rgb($to));
        imagealphablending($image, true);

        foreach (range(1, mt_rand(4, 7)) as $ignored) {
            $light = (bool) mt_rand(0, 1);
            $colour = imagecolorallocatealpha($image, $light ? 255 : 36, $light ? 255 : 40, $light ? 255 : 45, mt_rand(100, 118));
            $size = mt_rand((int) ($height * 0.3), (int) ($height * 0.95));

            imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), $size, $size, $colour);
        }

        ob_start();
        imagewebp($image, null, 80);
        $contents = (string) ob_get_clean();

        return $contents;
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     */
    private static function gradient(GdImage $image, int $width, int $height, array $from, array $to): void
    {
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $colour = imagecolorallocate(
                $image,
                (int) ($from[0] + ($to[0] - $from[0]) * $ratio),
                (int) ($from[1] + ($to[1] - $from[1]) * $ratio),
                (int) ($from[2] + ($to[2] - $from[2]) * $ratio),
            );

            imageline($image, 0, $y, $width, $y, $colour);
        }
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
