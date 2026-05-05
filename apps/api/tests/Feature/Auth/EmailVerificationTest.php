<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['view.engine_resolver' => function () {
        return function ($path, $data = []) {
            return '';
        };
    }]);
});

test('email can be verified', function () {
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
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertJson(['ok' => true], true);
});

test('email is not verified with invalid hash', function () {
    $this->withoutMiddleware();

    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['ulid' => $user->ulid, 'hash' => hash('sha256', 'wrong-email')]
    );

    $this->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
