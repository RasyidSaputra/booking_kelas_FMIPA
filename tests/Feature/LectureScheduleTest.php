<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LectureScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $lecturer;

    private int $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(9, 0));
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->lecturer = User::factory()->create(['role' => 'dosen']);
        $this->room = DB::table('rooms')->insertGetId([
            'code' => 'LECTURE-101', 'name' => 'Ruang Jadwal Kuliah',
            'capacity' => 50, 'location' => 'Gedung Kuliah', 'is_active' => true,
        ]);
        $this->actingAs($this->admin);
    }

    public function test_admin_creates_a_semester_once_and_all_weekly_meetings_block_the_calendar(): void
    {
        $this->get('/admin/lecture-schedules')->assertOk()->assertSeeText('Tambah jadwal mingguan');
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.lecture-schedules.index'));
        $this->assertDatabaseCount('lecture_schedules', 1);
        $meetings = DB::table('bookings')->whereNotNull('lecture_schedule_id')->orderBy('starts_at')->get();
        $this->assertCount(4, $meetings);
        $this->assertSame(['2026-11-02', '2026-11-09', '2026-11-16', '2026-11-23'], $meetings->pluck('lecture_date')->all());
        $this->assertTrue($meetings->every(fn ($meeting) => $meeting->status === 'approved' && $meeting->reviewed_by === $this->admin->id));

        $this->get('/dashboard?month=11&year=2026&date=2026-11-09')->assertOk()
            ->assertViewHas('selectedSchedule', fn ($schedule) => $schedule->has($this->room));
        $this->get('/rooms?date=2026-11-09')->assertOk()->assertSee('Kalkulus - Kelas A');
        $this->get('/admin/rooms?tab=schedules&date=2026-11-09')->assertOk()->assertSeeText('Edit pertemuan ini');
        $this->get('/admin/lecture-schedules?edit=1')->assertOk()->assertSeeText('Ubah jadwal berikutnya');
    }

    public function test_borrower_cannot_book_a_lecture_but_can_book_adjacent_times_and_weekends(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']));
        $this->from('/rooms/'.$this->room)->post('/bookings', $this->bookingData('2026-11-09', '09:00', '11:00'))
            ->assertSessionHas('error')->assertRedirect('/rooms/'.$this->room);
        $this->assertDatabaseCount('bookings', 4);
        foreach ([['2026-11-09', '10:00', '11:00'], ['2026-11-07', '08:00', '10:00'], ['2026-11-08', '08:00', '10:00']] as [$date, $start, $end]) {
            $this->post('/bookings', $this->bookingData($date, $start, $end))
                ->assertSessionHasNoErrors()->assertRedirect(route('bookings.index'));
        }
        $this->assertDatabaseCount('bookings', 7);
        $this->get('/rooms/'.$this->room)->assertOk()->assertSeeText('surat izin atau surat peminjaman ruangan');
    }

    public function test_pending_booking_is_checked_again_when_admin_approves_it(): void
    {
        $this->post('/bookings', $this->bookingData('2026-11-09', '09:00', '11:00'))->assertSessionHasNoErrors();
        $pending = DB::table('bookings')->value('id');
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $this->patchJson('/admin/bookings/'.$pending.'/review', ['decision' => 'approved'])
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertDatabaseHas('bookings', ['id' => $pending, 'status' => 'pending']);
    }

    public function test_the_room_becomes_available_at_the_exact_lecture_end_time(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $this->travelTo(now()->setDate(2026, 11, 2)->setTime(9, 59, 59));
        $this->get('/rooms/'.$this->room)->assertViewHas('currentBooking', fn ($booking) => $booking !== null);
        $this->travelTo(now()->setTime(10, 0, 0));
        $this->get('/rooms/'.$this->room)->assertViewHas('currentBooking', null);
        $this->get('/rooms')->assertViewHas('currentlyOccupiedCount', 0);
    }

    public function test_one_conflict_rolls_back_all_generated_meetings_and_the_pattern(): void
    {
        $this->postJson('/admin/schedules', $this->meetingData('2026-11-16', '09:00', '11:00'))->assertOk();
        $this->postJson('/admin/lecture-schedules', $this->data())->assertUnprocessable()
            ->assertJsonValidationErrors('starts_at')->assertSee('16-11-2026');
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_cancelling_one_meeting_opens_only_that_date(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $meeting = DB::table('bookings')->where('lecture_date', '2026-11-09')->first();
        $this->patchJson('/admin/schedules/'.$meeting->id.'/cancel')->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $meeting->id, 'status' => 'cancelled', 'is_lecture_exception' => true]);
        $this->post('/bookings', $this->bookingData('2026-11-09', '08:00', '10:00'))->assertRedirect(route('bookings.index'));
        $this->post('/bookings', $this->bookingData('2026-11-16', '08:00', '10:00'))->assertSessionHas('error');
        $this->assertSame(3, DB::table('bookings')->whereNotNull('lecture_schedule_id')->where('status', 'approved')->count());
    }

    public function test_one_meeting_can_move_without_changing_the_other_weeks(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $meeting = DB::table('bookings')->where('lecture_date', '2026-11-09')->first();
        $this->patchJson('/admin/schedules/'.$meeting->id, $this->meetingData('2026-11-10', '13:00', '15:00'))
            ->assertOk();
        $this->assertDatabaseHas('bookings', [
            'id' => $meeting->id, 'lecture_date' => '2026-11-09', 'is_lecture_exception' => true,
            'starts_at' => '2026-11-10 13:00:00',
        ]);
        $this->post('/bookings', $this->bookingData('2026-11-09', '08:00', '10:00'))->assertRedirect(route('bookings.index'));
        $this->post('/bookings', $this->bookingData('2026-11-10', '13:00', '15:00'))->assertSessionHas('error');
    }

    public function test_future_pattern_changes_preserve_history_and_individual_exceptions(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $meeting = DB::table('bookings')->where('lecture_date', '2026-11-16')->first();
        $this->patchJson('/admin/schedules/'.$meeting->id.'/cancel')->assertOk();
        $this->patch('/admin/lecture-schedules/1', $this->data([
            'start_time' => '10:00', 'end_time' => '12:00', 'effective_from' => '2026-11-09',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.lecture-schedules.index'));
        $this->assertDatabaseHas('bookings', ['lecture_date' => '2026-11-02', 'starts_at' => '2026-11-02 08:00:00', 'status' => 'approved']);
        $this->assertDatabaseHas('bookings', ['lecture_date' => '2026-11-09', 'starts_at' => '2026-11-09 10:00:00', 'status' => 'approved']);
        $this->assertDatabaseHas('bookings', ['id' => $meeting->id, 'status' => 'cancelled', 'is_lecture_exception' => true]);
        $this->assertSame(0, DB::table('bookings')->where('lecture_date', '2026-11-16')->where('status', 'approved')->count());
        $this->assertSame(3, DB::table('bookings')->where('status', 'approved')->count());
    }

    public function test_failed_future_update_preserves_the_original_pattern_and_occurrences(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $this->postJson('/admin/schedules', $this->meetingData('2026-11-23', '13:00', '15:00'))->assertOk();
        $before = DB::table('bookings')->orderBy('id')->get()->toJson();
        $this->patchJson('/admin/lecture-schedules/1', $this->data([
            'start_time' => '13:00', 'end_time' => '15:00', 'effective_from' => '2026-11-09',
        ]))->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertSame($before, DB::table('bookings')->orderBy('id')->get()->toJson());
        $this->assertDatabaseHas('lecture_schedules', ['id' => 1, 'start_time' => '08:00']);
    }

    public function test_stopping_a_series_releases_future_slots_and_keeps_earlier_meetings(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $this->patch('/admin/lecture-schedules/1/cancel', ['effective_from' => '2026-11-09'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.lecture-schedules.index'));
        $this->assertDatabaseCount('bookings', 4);
        $this->assertSame(1, DB::table('bookings')->where('status', 'approved')->count());
        $this->assertDatabaseHas('lecture_schedules', ['id' => 1, 'cancelled_from' => '2026-11-09']);
        $this->post('/bookings', $this->bookingData('2026-11-09', '08:00', '10:00'))->assertRedirect(route('bookings.index'));
    }

    public function test_past_meetings_are_not_generated_when_the_semester_has_already_started(): void
    {
        $this->post('/admin/lecture-schedules', $this->data(['starts_on' => '2026-10-01']))->assertSessionHasNoErrors();
        $this->assertSame(4, DB::table('bookings')->count());
        $this->assertSame(0, DB::table('bookings')->where('starts_at', '<', now())->count());
    }

    public function test_an_ongoing_meeting_is_preserved_when_the_future_pattern_changes(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $this->travelTo(now()->setDate(2026, 11, 9)->setTime(9, 0));
        $this->patch('/admin/lecture-schedules/1', $this->data([
            'start_time' => '13:00', 'end_time' => '15:00', 'effective_from' => '2026-11-09',
        ]))->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('bookings')->where('lecture_date', '2026-11-09')->count());
        $this->assertDatabaseHas('bookings', ['lecture_date' => '2026-11-09', 'starts_at' => '2026-11-09 08:00:00', 'status' => 'approved']);
        $this->assertDatabaseHas('bookings', ['lecture_date' => '2026-11-16', 'starts_at' => '2026-11-16 13:00:00', 'status' => 'approved']);
    }

    public function test_started_lecture_meetings_cannot_be_edited_or_cancelled_individually(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $meeting = DB::table('bookings')->where('lecture_date', '2026-11-02')->first();
        $this->travelTo(now()->setDate(2026, 11, 2)->setTime(9, 0));
        $this->patchJson('/admin/schedules/'.$meeting->id, $this->meetingData('2026-11-03', '08:00', '10:00'))
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->patchJson('/admin/schedules/'.$meeting->id.'/cancel')
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertDatabaseHas('bookings', ['id' => $meeting->id, 'starts_at' => '2026-11-02 08:00:00', 'status' => 'approved']);
    }

    #[DataProvider('invalidPatterns')]
    public function test_invalid_patterns_do_not_write_any_data(array $changes, string $field): void
    {
        $this->postJson('/admin/lecture-schedules', $this->data($changes))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 0);
    }

    public static function invalidPatterns(): array
    {
        return [
            'before opening' => [['start_time' => '07:00'], 'start_time'],
            'after closing' => [['end_time' => '18:01'], 'end_time'],
            'invalid interval' => [['end_time' => '08:00'], 'end_time'],
            'weekend lecture' => [['weekday' => 6], 'weekday'],
            'reversed period' => [['ends_on' => '2026-11-01'], 'ends_on'],
            'unbounded period' => [['ends_on' => '2028-11-23'], 'ends_on'],
            'no matching weekday' => [['starts_on' => '2026-11-03', 'ends_on' => '2026-11-03'], 'ends_on'],
            'too many participants' => [['participant_count' => 51], 'participant_count'],
        ];
    }

    public function test_inactive_rooms_and_retroactive_updates_are_rejected(): void
    {
        DB::table('rooms')->where('id', $this->room)->update(['is_active' => false]);
        $this->postJson('/admin/lecture-schedules', $this->data())->assertUnprocessable()->assertJsonValidationErrors('room_id');
        DB::table('rooms')->where('id', $this->room)->update(['is_active' => true]);
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        $this->patchJson('/admin/lecture-schedules/1', $this->data(['effective_from' => '2026-10-28']))
            ->assertUnprocessable()->assertJsonValidationErrors('effective_from');
        $this->patchJson('/admin/lecture-schedules/1/cancel', ['effective_from' => '2026-10-28'])
            ->assertUnprocessable()->assertJsonValidationErrors('effective_from');
    }

    public function test_students_and_lecturers_cannot_manage_recurring_lectures(): void
    {
        $this->post('/admin/lecture-schedules', $this->data())->assertSessionHasNoErrors();
        foreach (['mahasiswa', 'dosen'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/admin/lecture-schedules')->assertForbidden();
            $this->postJson('/admin/lecture-schedules', $this->data())->assertForbidden();
            $this->patchJson('/admin/lecture-schedules/1', $this->data(['effective_from' => '2026-11-02']))->assertForbidden();
            $this->patchJson('/admin/lecture-schedules/1/cancel', ['effective_from' => '2026-11-02'])->assertForbidden();
        }
    }

    private function data(array $changes = []): array
    {
        return array_replace([
            'room_id' => $this->room, 'user_id' => $this->lecturer->id,
            'semester' => 'Ganjil 2026/2027', 'purpose' => 'Kalkulus - Kelas A',
            'participant_count' => 40, 'weekday' => 1, 'start_time' => '08:00', 'end_time' => '10:00',
            'starts_on' => '2026-11-02', 'ends_on' => '2026-11-23',
        ], $changes);
    }

    private function bookingData(string $date, string $start, string $end): array
    {
        return [
            'room_id' => $this->room, 'booking_date' => $date, 'start_time' => $start, 'end_time' => $end,
            'purpose' => 'Kegiatan mahasiswa dengan surat izin', 'participant_count' => 20,
        ];
    }

    private function meetingData(string $date, string $start, string $end): array
    {
        return [
            'room_id' => $this->room, 'user_id' => $this->lecturer->id,
            'starts_at' => $date.' '.$start, 'ends_at' => $date.' '.$end,
            'purpose' => 'Pertemuan khusus', 'participant_count' => 30,
        ];
    }
}
