<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private int $bookingId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-19 10:00:00', 'Asia/Jakarta'));
        $this->owner = User::factory()->create();
        $roomId = DB::table('rooms')->insertGetId([
            'code' => 'DETAIL-101', 'name' => 'Ruang Detail', 'capacity' => 40,
            'location' => 'Gedung Detail', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->bookingId = DB::table('bookings')->insertGetId([
            'user_id' => $this->owner->id, 'room_id' => $roomId,
            'purpose' => 'Diskusi detail pengajuan', 'participant_count' => 15,
            'starts_at' => '2026-12-21 08:00:00', 'ends_at' => '2026-12-21 10:00:00',
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_guest_must_log_in(): void
    {
        $this->get(route('bookings.show', $this->bookingId))->assertRedirect(route('login'));
    }

    public function test_owner_can_read_details_and_open_them_from_the_list(): void
    {
        $this->actingAs($this->owner)->get(route('bookings.index'))
            ->assertOk()->assertSee(route('bookings.show', $this->bookingId));
        $this->get(route('bookings.show', $this->bookingId))->assertOk()
            ->assertSeeText(['Ruang Detail', 'Gedung Detail', 'Diskusi detail pengajuan', '08:00', '10:00', 'Menunggu'])
            ->assertSeeText('Silakan menunggu proses persetujuan.');
    }

    #[DataProvider('nonAdminRoles')]
    public function test_other_users_cannot_read_private_booking_details(string $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get(route('bookings.show', $this->bookingId))->assertForbidden()
            ->assertDontSeeText('Diskusi detail pengajuan');
    }

    public static function nonAdminRoles(): array
    {
        return [['mahasiswa'], ['dosen']];
    }

    public function test_admin_can_open_another_users_booking_and_return_to_admin_list(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('bookings.show', $this->bookingId))->assertOk()
            ->assertSeeText('Diskusi detail pengajuan')
            ->assertSee('href="'.route('admin.rooms.index', ['tab' => 'bookings']).'"', false);
    }

    public function test_admin_list_links_to_booking_details(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.rooms.index', ['tab' => 'bookings']))->assertOk()
            ->assertSee('href="'.route('bookings.show', $this->bookingId).'"', false);
    }

    public function test_missing_and_invalid_booking_ids_return_not_found(): void
    {
        $this->actingAs($this->owner)->get('/bookings/999999')->assertNotFound();
        $this->get('/bookings/not-a-number')->assertNotFound();
    }

    public function test_rejected_booking_shows_reviewer_time_and_escaped_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Pemeriksa Detail']);
        $reason = '<script>alert("xss")</script> Jadwal bentrok';
        DB::table('bookings')->where('id', $this->bookingId)->update([
            'status' => 'rejected', 'reviewed_by' => $admin->id,
            'reviewed_at' => '2026-12-20 09:15:00', 'rejection_reason' => $reason,
        ]);
        $this->actingAs($this->owner)->get(route('bookings.show', $this->bookingId))->assertOk()
            ->assertSeeText(['Ditolak', 'Admin Pemeriksa Detail', '20 Desember 2026, 09:15', 'Alasan Penolakan'])
            ->assertSee($reason)->assertDontSee($reason, false)
            ->assertDontSeeText('Silakan menunggu proses persetujuan.');
    }

    public function test_cancelled_pending_booking_no_longer_asks_owner_to_wait_for_approval(): void
    {
        $this->actingAs($this->owner)->patch(route('bookings.cancel', $this->bookingId))
            ->assertRedirect(route('bookings.index'))->assertSessionHas('success');
        $this->get(route('bookings.show', $this->bookingId))->assertOk()
            ->assertSeeText('Pengajuan telah dibatalkan.')
            ->assertDontSeeText('Silakan menunggu proses persetujuan.');
    }

    public function test_approved_booking_without_review_metadata_does_not_ask_owner_to_wait(): void
    {
        DB::table('bookings')->where('id', $this->bookingId)->update(['status' => 'approved']);
        $this->actingAs($this->owner)->get(route('bookings.show', $this->bookingId))->assertOk()
            ->assertSeeText('Disetujui')->assertDontSeeText('Silakan menunggu proses persetujuan.');
    }

    public function test_multi_day_booking_displays_both_dates(): void
    {
        DB::table('bookings')->where('id', $this->bookingId)->update(['ends_at' => '2026-12-22 10:00:00']);
        $this->actingAs($this->owner)->get(route('bookings.show', $this->bookingId))->assertOk()
            ->assertSeeText(['Senin, 21 Desember 2026', 'Selasa, 22 Desember 2026']);
    }
}
