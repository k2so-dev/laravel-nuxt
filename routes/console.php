<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('auth:clear-resets')->daily();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('temporary:clear')->hourly();

if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
    Schedule::command('telescope:prune --hours=24')
        ->daily()
        ->environments(['local']);
}

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
