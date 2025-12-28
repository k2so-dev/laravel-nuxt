<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure view engine to prevent file operations during testing
        config(['view.engine_resolver' => function () {
            return function ($path, $data = []) {
                return '';
            };
        }]);
    }

    public function test_email_can_be_verified(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['ulid' => $user->ulid, 'hash' => hash('sha256', $user->email)]
        );

        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertJson(['ok' => true], true);
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['ulid' => $user->ulid, 'hash' => hash('sha256', 'wrong-email')]
        );

        $this->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
