<?php

namespace App\Providers;

use App\Helpers\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Telescope only in local environment
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
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
         * Convert uploaded image to webp, jpeg or png format and resize it
         */
        UploadedFile::macro('convert', function (int $width = null, int $height = null, string $extension = 'webp', int $quality = 90): UploadedFile {
            return tap($this, function (UploadedFile $file) use ($width, $height, $extension, $quality) {
                Image::convert($file->path(), $file->path(), $width, $height, $extension, $quality);
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
