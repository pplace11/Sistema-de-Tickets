<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterCreatesClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_register_creates_client_user(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Novo Cliente',
            'email' => 'novo-cliente@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();

        $user = User::query()->where('email', 'novo-cliente@example.test')->first();

        $this->assertNotNull($user);
        $this->assertSame('client', $user->role);
        $this->assertTrue((bool) $user->is_active);
    }
}
