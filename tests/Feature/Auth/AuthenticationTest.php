<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $guard = config('auth.defaults.guard');

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ], [
            'Origin' => isset(config('sanctum.stateful')[0]) ? config('sanctum.stateful')[0] : 'localhost',
        ]);

        $response->assertStatus(200);

        if ($guard === 'api') {
            $response->assertJson(
                fn(AssertableJson $json) => $json
                    ->hasAll(['ok', 'token'])
            );
        } else {
            $response->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('ok')
                    ->where('ok', true)
                    ->missing('token')
            );
        }
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertJson(
            fn(AssertableJson $json) => $json
                ->hasAll(['ok', 'message', 'errors'])
                ->where('ok', false)
                ->missing('token')
        );
    }

    public function test_users_can_logout(): void
    {
        $guard = config('auth.defaults.guard');

        /** @var User $user */
        $user = User::factory()->create();

        if ($guard === 'api') {
            $token = $user->createDeviceToken('test-device', '127.0.0.1');
            $response = $this->post('/api/v1/logout', [], [
                'Authorization' => 'Bearer ' . $token,
            ]);
        } else {
            $this->actingAs($user, $guard);
            $response = $this->post('/api/v1/logout');
        }

        $response->assertJson(['ok' => true], true);
    }
}
