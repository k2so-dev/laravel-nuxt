<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Disable lazy loading in development
         */
        // Model::preventLazyLoading(!$this->app->isProduction());
        // Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());
    }
}
