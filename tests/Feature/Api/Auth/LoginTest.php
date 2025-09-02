<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user login
     *
     * @return void
     */
    public function test_user_can_login_successfully(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'token',
                ],
            ])
            ->assertJson([
                'message' => 'Login successful',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test login with invalid credentials
     *
     * @return void
     */
    public function test_login_with_invalid_credentials(): void
    {
        // Create a user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Try to login with wrong password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test validation errors during login
     *
     * @return void
     */
    public function test_validation_errors_on_login(): void
    {
        // Missing required fields
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);

        // Invalid email
        $response = $this->postJson('/api/auth/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
