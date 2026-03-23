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
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ], [
            'Origin' => isset(config('sanctum.stateful')[0]) ? config('sanctum.stateful')[0] : 'localhost',
        ]);

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) => $json
                ->has('ok')
                ->where('ok', true)
                ->missing('token')
        );
    }

    public function test_oauth_redirect_has_web_middleware_for_session_persistence(): void
    {
        // Issue #31: OAuth redirect should have 'web' middleware to maintain session
        // between redirect to provider and callback from provider

        // Test that the redirect route has web middleware for session persistence
        $routes = app('router')->getRoutes();
        $redirectRoute = null;

        foreach ($routes as $route) {
            if ($route->getName() === 'login.provider.redirect') {
                $redirectRoute = $route;
                break;
            }
        }

        $this->assertNotNull($redirectRoute, 'OAuth redirect route should exist');
        $this->assertContains('web', $redirectRoute->middleware(), 'OAuth redirect should have web middleware for session persistence');
    }

    public function test_session_based_logout_clears_server_session(): void
    {
        // Issue #31: SSR logout should clear session on server side
        // Currently, logout only clears client-side cookies but server-side
        // session remains, causing SSR to think user is still authenticated

        $user = User::factory()->create();

        // Simulate session-based login
        $this->actingAs($user, 'web');

        // Verify user is authenticated
        $this->assertAuthenticated('web');

        // Perform logout
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['ok' => true]);

        // After logout, session should be cleared
        $this->assertGuest('web');
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
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);
        $response = $this->post('/api/v1/logout');

        $response->assertJson(['ok' => true], true);
    }
}
