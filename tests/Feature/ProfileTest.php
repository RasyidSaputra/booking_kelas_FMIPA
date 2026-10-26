<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create(['identity_number' => '00123456789', 'role' => 'mahasiswa']);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()->assertSeeText('NIM / NIP')->assertSeeText('00123456789')->assertSeeText('Mahasiswa');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'identity_number' => '00123456789',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('00123456789', $user->identity_number);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create(['identity_number' => '00123456789']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'identity_number' => $user->identity_number,
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_guests_cannot_access_or_update_a_profile(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
        $this->patch('/profile', [])->assertRedirect(route('login'));
    }

    public function test_identity_number_and_email_must_be_unique(): void
    {
        $user = User::factory()->create(['identity_number' => '00111111111']);
        $other = User::factory()->create(['identity_number' => '00222222222']);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama yang belum boleh disimpan',
            'identity_number' => $other->identity_number,
            'email' => $other->email,
        ])->assertSessionHasErrors(['identity_number', 'email']);

        $this->assertSame('00111111111', $user->fresh()->identity_number);
        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_identity_number_is_required_when_saving_a_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'identity_number' => '',
            'email' => $user->email,
        ])->assertSessionHasErrors('identity_number');
    }

    public function test_borrower_cannot_promote_their_role_through_profile(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'identity_number' => '00123456789',
            'email' => $user->email,
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertSame('mahasiswa', $user->fresh()->role);
        $this->assertNull($user->fresh()->identity_number);
    }

    public function test_admin_role_cannot_be_changed_through_profile(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'identity_number' => '00123456789',
            'email' => $user->email,
            'role' => 'dosen',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $user->fresh()->role);
    }

    public function test_profile_only_updates_the_logged_in_user_and_allowed_fields(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama baru saya',
            'identity_number' => '00123456789',
            'email' => $user->email,
            'id' => $other->id,
            'user_id' => $other->id,
            'password' => 'injected-password',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertSame('Nama baru saya', $user->fresh()->name);
        $this->assertSame($other->name, $other->fresh()->name);
        $this->assertNull($other->fresh()->identity_number);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_an_account_with_booking_history_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $this->createBooking($user);

        $this->actingAs($user)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_an_account_with_review_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $this->createBooking($owner, $admin);

        $this->actingAs($admin)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->fresh());
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    private function createBooking(User $owner, ?User $reviewer = null): void
    {
        $room = DB::table('rooms')->insertGetId([
            'code' => 'TEST-01', 'name' => 'Ruang uji', 'capacity' => 30,
            'location' => 'FMIPA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('bookings')->insert([
            'user_id' => $owner->id, 'room_id' => $room, 'purpose' => 'Diskusi kelompok',
            'participant_count' => 10, 'starts_at' => '2026-10-26 08:00:00',
            'ends_at' => '2026-10-26 09:00:00', 'status' => 'approved',
            'reviewed_by' => $reviewer?->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
