<?php

namespace App\Providers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Telescope only in local environment
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Convert uploaded image to any format and resize it
         */
        UploadedFile::macro('convert', function (int $width = null, int $height = null, string $extension = 'webp', int $quality = 90): UploadedFile {
            return tap($this, function (UploadedFile $file) use ($width, $height, $extension, $quality) {
                $image = Image::make($file->path());
                $image->orientate();

                $maxSize = 1920;

                if ($width && $height) {
                    $image->fit($width, $height);
                } elseif ($width || $image->width() > $maxSize) {
                    $image->resize($width ?? $maxSize, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } elseif ($height || $image->height() > $maxSize) {
                    $image->resize(null, $height ?? $maxSize, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                $image->save($file->path(), $quality, $extension);
                $image->destroy();
            });
        });

        /**
         * Remove all special characters from a string
         */
        Str::macro('onlyWords', function (string $text): string {
            // \p{L} matches any kind of letter from any language
            // \d matches a digit in any script
            return static::replaceMatches('/[^\p{L}\d ]/u', '', $text);
        });
    }
}
