<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

use function Laravel\Prompts\select;

class AuthSwitch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:switch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch authentication guard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentGuard = config('auth.defaults.guard');
        $guard = select(
            label: 'Select the authentication guard',
            options: [
                'api' => 'API - Token based' . ($currentGuard === 'api' ? ' (Current)' : ''),
                'web' => 'Web - Session based' . ($currentGuard === 'web' ? ' (Current)' : ''),
            ],
        );

        if ($currentGuard === $guard) {
            $this->info('Authentication guard is already ' . $guard . '!');
            return;
        }

        $this->info('Switching authentication guard to ' . $guard . '...');

        Env::writeVariable('AUTH_GUARD', $guard, base_path('.env'), true);

        Role::query()->update(['guard_name' => $guard]);

        if ($guard === 'web') {
            $this->replaceByPattern(base_path('bootstrap/app.php'), '->statefulApi()', true);
        } else if ($guard === 'api') {
            $this->replaceByPattern(base_path('bootstrap/app.php'), '->statefulApi()', false);
        }

        Artisan::call('optimize');

        $this->info('Authentication guard switched to ' . $guard . ' successfully!');
    }

    private function replaceByPattern(string $path, string $pattern, bool $enable): void
    {
        $content = $contentReplaced = file_get_contents($path);

        if (!preg_match('@' . preg_quote($pattern) . '@', $content)) {
            $this->fail('Pattern not found in ' . $path);
        }

        if ($enable) {
            $contentReplaced = preg_replace('@([/]+[ ]*)?' . preg_quote($pattern) . '@', $pattern, $content);
        } else {
            $contentReplaced = preg_replace('@([/]+[ ]*)?' . preg_quote($pattern) . '@', '//' . $pattern, $content);
        }

        if ($contentReplaced !== $content) {
            file_put_contents($path, $contentReplaced);
        }
    }
}
