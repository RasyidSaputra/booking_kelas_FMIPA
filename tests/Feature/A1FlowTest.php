<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class A1FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_can_complete_registration_login_dashboard_profile_and_logout(): void
    {
        $this->get('/register')->assertOk();
        $this->post('/register', [
            'name' => 'Mahasiswa Pengujian A1',
            'identity_number' => '00123456789',
            'email' => 'mahasiswa-a1@example.com',
            'password' => 'password-awal-a1',
            'password_confirmation' => 'password-awal-a1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'mahasiswa-a1@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('mahasiswa', $user->role);
        $this->assertTrue(Hash::check('password-awal-a1', $user->password));

        // Registrasi Breeze langsung login. Logout dahulu untuk menguji
        // halaman login dan autentikasi NIM/NIP secara terpisah.
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/login')->assertOk();
        $this->post('/login', [
            'identity_number' => '00123456789',
            'password' => 'password-awal-a1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);

        $this->get('/dashboard')->assertOk()->assertSeeText('Bookingan saya')->assertDontSeeText('Master data kelas');
        $this->get('/profile')->assertOk()->assertSeeText('Mahasiswa Pengujian A1');
        $this->patch('/profile', [
            'name' => 'Nama Baru Mahasiswa A1',
            'identity_number' => '00123456790',
            'email' => 'profil-baru-a1@example.com',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

        $updated = $user->fresh();
        $this->assertSame('Nama Baru Mahasiswa A1', $updated->name);
        $this->assertSame('00123456790', $updated->identity_number);
        $this->assertSame('profil-baru-a1@example.com', $updated->email);
        $this->assertSame('mahasiswa', $updated->role);
        $this->get('/profile')->assertOk()->assertSeeText('Nama Baru Mahasiswa A1')->assertSeeText('00123456790');

        $this->from('/profile')->put('/password', [
            'current_password' => 'password-awal-a1',
            'password' => 'password-baru-a1',
            'password_confirmation' => 'password-baru-a1',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->assertTrue(Hash::check('password-baru-a1', $user->fresh()->password));

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->post('/login', [
            'identity_number' => '00123456789',
            'password' => 'password-baru-a1',
        ])->assertSessionHasErrors('identity_number');
        $this->assertGuest();
        $this->post('/login', [
            'identity_number' => '00123456790',
            'password' => 'password-awal-a1',
        ])->assertSessionHasErrors('identity_number');
        $this->assertGuest();
        $this->post('/login', [
            'identity_number' => '00123456790',
            'password' => 'password-baru-a1',
        ])->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    #[DataProvider('borrowerRoles')]
    public function test_mahasiswa_and_dosen_receive_403_for_room_master(string $role): void
    {
        $user = User::factory()->create(['role' => $role, 'identity_number' => '00123456789']);
        $this->post('/login', ['identity_number' => $user->identity_number, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->get('/dashboard')->assertOk()->assertDontSeeText('Master data kelas');
        $this->get('/admin/rooms')->assertForbidden();
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_log_in_and_view_room_master(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'identity_number' => '00999999999']);
        $this->post('/login', ['identity_number' => $admin->identity_number, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->get('/dashboard')->assertOk()->assertSeeText('Master data kelas');
        $this->get('/admin/rooms')->assertOk()->assertSeeText('Master data kelas');
        $this->assertAuthenticatedAs($admin);
    }

    #[DataProvider('allRoles')]
    public function test_booking_history_remains_private_with_forged_filters_and_pagination(string $role): void
    {
        $user = User::factory()->create(['role' => $role, 'identity_number' => '00123456789']);
        $other = User::factory()->create(['identity_number' => '00987654321']);
        $room = DB::table('rooms')->insertGetId([
            'code' => 'A1-01', 'name' => 'Ruang Uji A1', 'capacity' => 30,
            'location' => 'FMIPA', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        for ($day = 1; $day <= 12; $day++) {
            DB::table('bookings')->insert([
                'user_id' => $user->id, 'room_id' => $room,
                'purpose' => 'Agenda milik saya '.$day, 'participant_count' => 10,
                'starts_at' => sprintf('2026-11-%02d 08:00:00', $day),
                'ends_at' => sprintf('2026-11-%02d 09:00:00', $day),
                'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('bookings')->insert([
            'user_id' => $other->id, 'room_id' => $room,
            'purpose' => 'Agenda rahasia pengguna lain', 'participant_count' => 10,
            'starts_at' => '2026-11-15 08:00:00', 'ends_at' => '2026-11-15 09:00:00',
            'status' => 'approved', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->post('/login', ['identity_number' => $user->identity_number, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
        foreach ([1 => 10, 2 => 2] as $page => $expectedCount) {
            $this->get('/bookings?'.http_build_query(['user_id' => $other->id, 'role' => 'admin', 'page' => $page]))
                ->assertOk()->assertDontSeeText('Agenda rahasia pengguna lain')
                ->assertViewHas('bookings', fn ($bookings): bool => $bookings->total() === 12
                    && $bookings->count() === $expectedCount
                    && $bookings->getCollection()->every(fn ($booking): bool => $booking->user_id === $user->id));
        }
    }

    public static function borrowerRoles(): array
    {
        return ['mahasiswa' => ['mahasiswa'], 'dosen' => ['dosen']];
    }

    public static function allRoles(): array
    {
        return [...self::borrowerRoles(), 'admin' => ['admin']];
    }
}
