<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) => $json
            ->hasAll(['ok', 'token'])
        );
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertJson(fn (AssertableJson $json) => $json
            ->hasAll(['ok', 'message', 'errors'])
            ->missing('token')
        );
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'ulid' => Str::ulid()->toBase32(),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->post('/api/v1/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertJson(['ok' => true], true);
    }
}
