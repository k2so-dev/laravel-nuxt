<?php

namespace App\Providers;

use App\Helpers\Image;
use App\Helpers\Utils;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Telescope only in local environment
        if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class) && $this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', static function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('verification-notification', static function (Request $request) {
            return Limit::perMinute(1)->by($request->user()?->email ?: $request->ip());
        });

        RateLimiter::for('uploads', static function (Request $request) {
            return $request->user()?->hasRole('admin')
                ? Limit::none()
                : Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('login', static function (Request $request) {
            return Limit::perMinute(5)
                ->by(Str::transliterate(implode('|', [
                    strtolower($request->input('email')),
                    $request->ip()
                ])))
                ->response(static function (Request $request, array $headers): void {
                    event(new Lockout($request));

                    throw ValidationException::withMessages([
                        'email' => trans('auth.throttle', [
                            'seconds' => $headers['Retry-After'],
                            'minutes' => ceil($headers['Retry-After'] / 60),
                        ]),
                    ]);
                });
        });

        ResetPassword::createUrlUsing(static function (object $notifiable, string $token) {
            return config('app.frontend_url') . '/auth/reset/' . $token . '?email=' . $notifiable->getEmailForPasswordReset();
        });

        VerifyEmail::createUrlUsing(static function (object $notifiable) {
            $url = url()->temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'ulid' => $notifiable->ulid,
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return config('app.frontend_url') . '/auth/verify?verify_url=' . urlencode($url);
        });

        /**
         * Convert uploaded image to webp, jpeg or png format and resize it
         */
        UploadedFile::macro('convert', function (?int $width = null, ?int $height = null, string $extension = 'webp', int $quality = 90) {
            return tap($this, static function (UploadedFile $file) use ($width, $height, $extension, $quality) {
                Image::convert($file->path(), $file->path(), $width, $height, $extension, $quality);
            });
        });

        /**
         * Remove all special characters from a string
         */
        Str::macro('onlyWords', static function (string $text): string {
            // \p{L} matches any kind of letter from any language
            // \d matches a digit in any script
            return Str::replaceMatches('/[^\p{L}\d ]/u', '', $text);
        });

        Request::macro('device', function () {
            return Utils::getDeviceDetectorByUserAgent($this->userAgent());
        });

        Request::macro('deviceName', function (): string {
            return Utils::getDeviceNameFromDetector($this->device());
        });

        if (config('auth.defaults.guard') === 'api') {
            Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        }
    }
}
