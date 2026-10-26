<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'identity_number' => '00123456789',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'identity_number' => '00123456789',
            'role' => 'mahasiswa',
        ]);
        $this->assertTrue(Hash::check('password', User::where('email', 'test@example.com')->firstOrFail()->password));
    }

    public function test_public_registration_cannot_create_an_admin(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'identity_number' => '00123456789',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'role' => 'mahasiswa']);
    }

    public function test_public_registration_cannot_choose_a_lecturer_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'identity_number' => '00123456789',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'dosen',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'role' => 'mahasiswa']);
    }

    public function test_registration_requires_an_identity_number(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('identity_number');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_registration_rejects_a_duplicate_identity_number(): void
    {
        User::factory()->create(['identity_number' => '00123456789']);

        $response = $this->post('/register', [
            'name' => 'Another User',
            'identity_number' => '00123456789',
            'email' => 'another@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('identity_number');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'another@example.com']);
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'identity_number' => '00123456789',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
