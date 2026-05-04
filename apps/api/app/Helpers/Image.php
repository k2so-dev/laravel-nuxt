<?php

namespace App\Helpers;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class Image
{
    /**
     * Convert image to webp, jpeg or png format and resize it
     */
    public static function convert(string $source, string $target, ?int $width = null, ?int $height = null, string $extension = 'webp', int $quality = 90): void
    {
        $manager = new ImageManager(new GdDriver);

        $image = $manager->read($source);

        $maxSize = 1920;

        if ($width && $height) {
            $image->cover($width, $height);
        } elseif ($width || $image->width() > $maxSize) {
            $image->scale(width: $width ?? $maxSize);
        } elseif ($height || $image->height() > $maxSize) {
            $image->scale(height: $height ?? $maxSize);
        }

        if ($extension === 'webp') {
            $image->toWebp($quality)->save($target);
        } else if ($extension === 'jpeg') {
            $image->toJpeg($quality)->save($target);
        } else if ($extension === 'png') {
            $image->toPng()->save($target);
        }
    }
}
