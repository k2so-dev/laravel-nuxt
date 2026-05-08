<?php

namespace App\Helpers;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class Image
{
    public static function convert(string $source, string $target, ?int $width = null, ?int $height = null, string $extension = 'webp', int $quality = 90): void
    {
        $manager = new ImageManager(new GdDriver);

        $image = $manager->decode($source);

        $maxSize = 1920;

        if ($width && $height) {
            $image->cover($width, $height);
        } elseif ($width || $image->width() > $maxSize) {
            $image->scale(width: $width ?? $maxSize);
        } elseif ($height || $image->height() > $maxSize) {
            $image->scale(height: $height ?? $maxSize);
        }

        if ($extension === 'webp') {
            $image->encode(new WebpEncoder(quality: $quality))->save($target);
        } elseif ($extension === 'jpeg') {
            $image->encode(new JpegEncoder(quality: $quality))->save($target);
        } else {
            $image->encode(new PngEncoder())->save($target);
        }
    }
}
