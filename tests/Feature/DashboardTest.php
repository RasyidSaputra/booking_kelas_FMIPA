<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (['/dashboard', '/rooms', '/bookings', '/admin/rooms'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_dashboard_defaults_to_the_current_month_and_has_no_fake_availability(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertOk()
            ->assertViewHas('month', fn ($month): bool => $month->month === now()->month && $month->year === now()->year)
            ->assertViewHas('days', fn (array $days): bool => collect($days)->where('in_month', true)->every(fn (array $day): bool => $day['status'] === 'empty'))
            ->assertSee('Belum ada ruang aktif')
            ->assertDontSee('Master data kelas');
    }

    public function test_month_and_year_filters_support_leap_years(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard?month=2&year=2028')
            ->assertOk()
            ->assertViewHas('month', fn ($month): bool => $month->month === 2 && $month->year === 2028)
            ->assertViewHas('days', fn (array $days): bool => collect($days)->where('in_month', true)->count() === 29);
    }

    public function test_calendar_filters_reject_invalid_values(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard?month=13&year=1999&date=2026-02-30')
            ->assertSessionHasErrors(['month', 'year', 'date']);
    }

    public function test_occupancy_counts_distinct_active_rooms_with_approved_bookings_only(): void
    {
        $user = User::factory()->create();
        $rooms = $this->rooms(5);
        $inactive = $this->rooms(1, false)[0];
        $this->booking($user, $rooms[0], '2026-10-05 08:00:00', '2026-10-05 09:00:00');
        $this->booking($user, $rooms[0], '2026-10-05 10:00:00', '2026-10-05 11:00:00');
        $this->booking($user, $inactive, '2026-10-05 08:00:00', '2026-10-05 09:00:00');
        foreach (['pending', 'rejected', 'cancelled'] as $index => $status) {
            $this->booking($user, $rooms[$index + 1], '2026-10-05 08:00:00', '2026-10-05 09:00:00', $status);
        }

        $this->actingAs($user)->get('/dashboard?month=10&year=2026&date=2026-10-05')
            ->assertOk()->assertViewHas('days', function (array $days): bool {
                $day = collect($days)->first(fn (array $day): bool => $day['date']->toDateString() === '2026-10-05');

                return $day['occupied'] === 1 && $day['free'] === 4 && $day['status'] === 'quiet';
            });
    }

    public function test_calendar_colors_change_at_forty_and_eighty_percent(): void
    {
        $user = User::factory()->create();
        $rooms = $this->rooms(5);
        foreach ([1 => 1, 2 => 2, 3 => 4] as $day => $count) {
            foreach (array_slice($rooms, 0, $count) as $room) {
                $this->booking($user, $room, "2026-10-0{$day} 08:00:00", "2026-10-0{$day} 09:00:00");
            }
        }

        $this->actingAs($user)->get('/dashboard?month=10&year=2026')
            ->assertOk()->assertViewHas('days', function (array $days): bool {
                $byDate = collect($days)->keyBy(fn (array $day): string => $day['date']->toDateString());

                return $byDate['2026-10-01']['status'] === 'quiet'
                    && $byDate['2026-10-02']['status'] === 'moderate'
                    && $byDate['2026-10-03']['status'] === 'busy';
            });
    }

    public function test_overnight_bookings_use_exclusive_end_boundaries(): void
    {
        $user = User::factory()->create();
        $room = $this->rooms(1)[0];
        $this->booking($user, $room, '2026-09-30 23:00:00', '2026-10-01 00:00:00');
        $this->booking($user, $room, '2026-10-02 23:00:00', '2026-10-03 01:00:00');
        $this->booking($user, $room, '2026-10-04 23:00:00', '2026-10-05 00:00:00');

        $this->actingAs($user)->get('/dashboard?month=10&year=2026')
            ->assertOk()->assertViewHas('days', function (array $days): bool {
                $byDate = collect($days)->keyBy(fn (array $day): string => $day['date']->toDateString());

                return $byDate['2026-10-01']['occupied'] === 0
                    && $byDate['2026-10-02']['occupied'] === 1
                    && $byDate['2026-10-03']['occupied'] === 1
                    && $byDate['2026-10-04']['occupied'] === 1
                    && $byDate['2026-10-05']['occupied'] === 0;
            });
    }

    public function test_a_borrower_cannot_open_room_master_even_by_direct_url(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']))
            ->get('/admin/rooms')->assertForbidden();
    }

    public function test_a_lecturer_cannot_open_room_master(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'dosen']))
            ->get('/admin/rooms')->assertForbidden();
    }

    public function test_admin_sees_room_master_and_inactive_rooms(): void
    {
        $this->rooms(1, false);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Master data kelas');
        $this->actingAs($admin)->get('/admin/rooms')->assertOk()->assertSee('Nonaktif');
    }

    public function test_booking_history_is_scoped_to_the_logged_in_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $room = $this->rooms(1)[0];
        $ownId = $this->booking($user, $room, '2026-10-05 08:00:00', '2026-10-05 09:00:00');
        $otherId = $this->booking($other, $room, '2026-10-06 08:00:00', '2026-10-06 09:00:00');
        DB::table('bookings')->where('id', $ownId)->update(['purpose' => 'Kegiatan milik saya']);
        DB::table('bookings')->where('id', $otherId)->update(['purpose' => 'Kegiatan pribadi pengguna lain']);

        $this->actingAs($user)->get('/bookings')->assertOk()
            ->assertSee('Kegiatan milik saya')->assertDontSee('Kegiatan pribadi pengguna lain');
    }

    public function test_room_search_only_returns_matching_active_rooms(): void
    {
        $rooms = $this->rooms(2);
        DB::table('rooms')->where('id', $rooms[0])->update(['name' => 'Laboratorium Fisika']);
        DB::table('rooms')->where('id', $rooms[1])->update(['name' => 'Laboratorium Kimia']);
        $inactive = $this->rooms(1, false)[0];
        DB::table('rooms')->where('id', $inactive)->update(['name' => 'Fisika Nonaktif']);

        $this->actingAs(User::factory()->create())->get('/rooms?q=Fisika')
            ->assertOk()->assertSee('Laboratorium Fisika')
            ->assertDontSee('Laboratorium Kimia')->assertDontSee('Fisika Nonaktif');
    }

    private function rooms(int $count, bool $active = true): array
    {
        $ids = [];
        for ($index = 0; $index < $count; $index++) {
            $code = 'R-'.(DB::table('rooms')->count() + 1);
            $ids[] = DB::table('rooms')->insertGetId([
                'code' => $code, 'name' => 'Ruang '.$code, 'capacity' => 30,
                'location' => 'Gedung FMIPA', 'is_active' => $active,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $ids;
    }

    private function booking(User $user, int $room, string $start, string $end, string $status = 'approved'): int
    {
        return DB::table('bookings')->insertGetId([
            'user_id' => $user->id, 'room_id' => $room, 'purpose' => 'Kegiatan kampus',
            'participant_count' => 20, 'starts_at' => $start, 'ends_at' => $end,
            'status' => $status, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
