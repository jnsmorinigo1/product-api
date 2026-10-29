<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_list_products(): void
    {
        $this->getJson('/products')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_login_issues_a_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'ada@example.com');

        $this->withToken($response->json('token'))
            ->getJson('/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
        ]);

        $this->postJson('/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)
            ->postJson('/auth/logout')
            ->assertNoContent();

        // The test uses the same app, so we forget the user before the next request.
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/products')
            ->assertUnauthorized();
    }

    public function test_the_seeded_demo_user_can_log_in(): void
    {
        $this->seed();

        $this->postJson('/auth/login', [
            'email' => 'demo@example.com',
            'password' => 'password',
        ])->assertOk();
    }
}
