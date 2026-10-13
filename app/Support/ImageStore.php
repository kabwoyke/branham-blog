<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageStore
{
    public const DISK = 'public';

    public const MAX_WIDTH = 1600;

    /**
     * Resize an uploaded image to at most MAX_WIDTH pixels wide and store it as WebP.
     *
     * @return string Path relative to the public disk.
     */
    public static function store(UploadedFile $file, string $directory = 'articles'): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($source === false) {
            throw new RuntimeException('The uploaded file is not a readable image.');
        }

        $width = imagesx($source);

        if ($width > self::MAX_WIDTH) {
            $scaled = imagescale($source, self::MAX_WIDTH);

            if ($scaled !== false) {
                $source = $scaled;
            }
        }

        imagepalettetotruecolor($source);
        imagealphablending($source, true);
        imagesavealpha($source, true);

        ob_start();
        imagewebp($source, null, 82);
        $contents = (string) ob_get_clean();

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk(self::DISK)->put($path, $contents);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
