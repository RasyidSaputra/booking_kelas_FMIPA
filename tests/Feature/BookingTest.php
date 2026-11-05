<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private int $roomId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'mahasiswa', 'identity_number' => '12345678']);
        $this->roomId = DB::table('rooms')->insertGetId([
            'code' => 'R-101',
            'name' => 'Ruang Teori 101',
            'capacity' => 40,
            'location' => 'Gedung A FMIPA',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_user_can_view_bookings_page_with_action_buttons(): void
    {
        $tomorrow = Carbon::tomorrow();
        DB::table('bookings')->insert([
            'user_id' => $this->user->id,
            'room_id' => $this->roomId,
            'purpose' => 'Diskusi Belajar',
            'participant_count' => 15,
            'starts_at' => $tomorrow->copy()->setTime(8, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(10, 0)->toDateTimeString(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->get('/bookings')
            ->assertOk()
            ->assertSee('Bookingan saya')
            ->assertSee('Revisi Waktu')
            ->assertSee('Batalkan');
    }

    public function test_user_can_cancel_pending_booking(): void
    {
        $tomorrow = Carbon::tomorrow();
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $this->user->id,
            'room_id' => $this->roomId,
            'purpose' => 'Rapat Himpunan',
            'participant_count' => 20,
            'starts_at' => $tomorrow->copy()->setTime(9, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(11, 0)->toDateTimeString(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->patch('/bookings/' . $bookingId . '/cancel')
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('success', 'Booking berhasil dibatalkan.');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'cancelled',
        ]);
    }

    public function test_user_can_cancel_approved_booking(): void
    {
        $tomorrow = Carbon::tomorrow();
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $this->user->id,
            'room_id' => $this->roomId,
            'purpose' => 'Kuliah Pengganti',
            'participant_count' => 30,
            'starts_at' => $tomorrow->copy()->setTime(13, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(15, 0)->toDateTimeString(),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->patch('/bookings/' . $bookingId . '/cancel')
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('success', 'Booking berhasil dibatalkan.');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'cancelled',
        ]);
    }

    public function test_user_cannot_cancel_other_users_booking(): void
    {
        $otherUser = User::factory()->create(['role' => 'mahasiswa', 'identity_number' => '87654321']);
        $tomorrow = Carbon::tomorrow();
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $otherUser->id,
            'room_id' => $this->roomId,
            'purpose' => 'Agenda Orang Lain',
            'participant_count' => 10,
            'starts_at' => $tomorrow->copy()->setTime(9, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(10, 0)->toDateTimeString(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->patch('/bookings/' . $bookingId . '/cancel')
            ->assertForbidden();

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_revise_booking_time_for_pending_booking(): void
    {
        $tomorrow = Carbon::tomorrow();
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $this->user->id,
            'room_id' => $this->roomId,
            'purpose' => 'Seminar Kecil',
            'participant_count' => 10,
            'starts_at' => $tomorrow->copy()->setTime(8, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(9, 0)->toDateTimeString(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->patch('/bookings/' . $bookingId, [
                'booking_date' => $tomorrow->toDateString(),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'purpose' => 'Seminar Kecil Revisi',
                'participant_count' => 15,
            ])
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'starts_at' => $tomorrow->copy()->setTime(10, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(12, 0)->toDateTimeString(),
            'purpose' => 'Seminar Kecil Revisi',
            'participant_count' => 15,
            'status' => 'pending',
        ]);
    }

    public function test_user_revising_approved_booking_reverts_status_to_pending_for_reapproval(): void
    {
        $tomorrow = Carbon::tomorrow();
        $admin = User::factory()->create(['role' => 'admin']);
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $this->user->id,
            'room_id' => $this->roomId,
            'purpose' => 'Praktikum Pengganti',
            'participant_count' => 20,
            'starts_at' => $tomorrow->copy()->setTime(8, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(10, 0)->toDateTimeString(),
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->patch('/bookings/' . $bookingId, [
                'booking_date' => $tomorrow->toDateString(),
                'start_time' => '13:00',
                'end_time' => '15:00',
                'purpose' => 'Praktikum Pengganti Pindah Jam',
                'participant_count' => 20,
            ])
            ->assertRedirect(route('bookings.index'))
            ->assertSessionHas('success', 'Waktu booking berhasil direvisi dan diajukan ulang untuk persetujuan Admin.');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'starts_at' => $tomorrow->copy()->setTime(13, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(15, 0)->toDateTimeString(),
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    public function test_user_cannot_revise_booking_to_conflicting_approved_slot(): void
    {
        $tomorrow = Carbon::tomorrow();
        $otherUser = User::factory()->create(['role' => 'dosen']);

        // Existing approved booking in the same room from 08:00 to 11:00
        DB::table('bookings')->insert([
            'user_id' => $otherUser->id,
            'room_id' => $this->roomId,
            'purpose' => 'Kuliah Reguler',
            'participant_count' => 30,
            'starts_at' => $tomorrow->copy()->setTime(8, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(11, 0)->toDateTimeString(),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User's own booking at 13:00 to 15:00
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $this->user->id,
            'room_id' => $this->roomId,
            'purpose' => 'Diskusi',
            'participant_count' => 10,
            'starts_at' => $tomorrow->copy()->setTime(13, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(15, 0)->toDateTimeString(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User tries to revise time to 09:00 - 10:30 (overlaps with approved booking)
        $this->actingAs($this->user)
            ->patch('/bookings/' . $bookingId, [
                'booking_date' => $tomorrow->toDateString(),
                'start_time' => '09:00',
                'end_time' => '10:30',
                'purpose' => 'Diskusi',
                'participant_count' => 10,
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Maaf, ruangan sudah dipesan pada jam tersebut.');

        // Booking remains untouched
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'starts_at' => $tomorrow->copy()->setTime(13, 0)->toDateTimeString(),
            'ends_at' => $tomorrow->copy()->setTime(15, 0)->toDateTimeString(),
        ]);
    }
}
