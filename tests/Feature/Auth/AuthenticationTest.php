<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['identity_number' => '00123456789']);

        $response = $this->post('/login', [
            'identity_number' => $user->identity_number,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['identity_number' => '00123456789']);

        $this->post('/login', [
            'identity_number' => $user->identity_number,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('identity_number');

        $this->assertGuest();
    }

    public function test_user_model_hashes_a_plain_password_before_storing_it(): void
    {
        $user = User::factory()->create([
            'identity_number' => '00123456789',
            'password' => 'local-test-password',
        ]);
        $stored = $user->fresh()->getRawOriginal('password');

        $this->assertSame('bcrypt', Hash::info($stored)['algoName']);
        $this->assertTrue(Hash::check('local-test-password', $stored));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
