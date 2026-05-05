<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

test('reset password link can be requested', function () {
    $this->withoutMiddleware();

    $user = User::factory()->create();

    $response = $this->post('/api/v1/forgot-password', ['email' => $user->email]);

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json) => $json
        ->hasAll(['ok', 'message'])
    );
});

test('password can be reset with valid token', function () {
    $this->withoutMiddleware();
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/api/v1/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $response = $this->post('/api/v1/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertJson(fn (AssertableJson $json) => $json
            ->hasAll(['ok', 'message'])
            ->missing('errors')
        );

        return true;
    });
});
