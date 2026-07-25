<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_is_rate_limited_by_email_and_ip(): void
    {
        User::factory()->create([
            'email' => 'target@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Six attempts are permitted per minute for a given email + IP.
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => 'target@example.com',
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        // The seventh is throttled before it reaches the controller.
        $this->postJson('/api/auth/login', [
            'email' => 'target@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_api_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/auth/register', [
                'name' => 'Spammer',
                'email' => 'spam@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
        }

        $this->postJson('/api/auth/register', [
            'name' => 'Spammer',
            'email' => 'spam@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(429);
    }
}
