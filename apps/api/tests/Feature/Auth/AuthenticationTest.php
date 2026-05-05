<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ], [
        'Origin' => isset(config('sanctum.stateful')[0]) ? config('sanctum.stateful')[0] : 'localhost',
    ]);

    $response->assertStatus(200);

    $response->assertJson(
        fn (AssertableJson $json) => $json
            ->has('ok')
            ->where('ok', true)
            ->missing('token')
    );
});

test('oauth redirect has web middleware for session persistence', function () {
    $routes = app('router')->getRoutes();
    $redirectRoute = null;

    foreach ($routes as $route) {
        if ($route->getName() === 'login.provider.redirect') {
            $redirectRoute = $route;
            break;
        }
    }

    expect($redirectRoute)->not->toBeNull();
    expect($redirectRoute->middleware())->toContain('web');
});

test('session based logout clears server session', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web');
    $this->assertAuthenticated('web');

    $response = $this->postJson('/api/v1/logout');

    $response->assertStatus(200)
        ->assertJson(['ok' => true]);

    $this->assertGuest('web');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertJson(
        fn (AssertableJson $json) => $json
            ->hasAll(['ok', 'message', 'errors'])
            ->where('ok', false)
            ->missing('token')
    );
});

test('users can logout', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);
    $response = $this->post('/api/v1/logout');

    $response->assertJson(['ok' => true], true);
});
