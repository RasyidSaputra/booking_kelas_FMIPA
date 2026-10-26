<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminMasterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    public function test_admin_can_open_all_master_tabs_without_exposing_password_hashes(): void
    {
        foreach (['rooms', 'facilities', 'bookings', 'schedules', 'users'] as $tab) {
            $this->get('/admin/rooms?tab='.$tab)->assertOk()->assertSee('master-dialog', false)
                ->assertDontSee($this->admin->password, false);
        }
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admins_cannot_mutate_any_master_data(string $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]));
        foreach (['/admin/rooms', '/admin/facilities', '/admin/users', '/admin/schedules'] as $path) {
            $this->postJson($path, [])->assertForbidden();
        }
        foreach (['/admin/rooms/1', '/admin/facilities/1', '/admin/users/1', '/admin/schedules/1', '/admin/bookings/1/review', '/admin/schedules/1/cancel'] as $path) {
            $this->patchJson($path, [])->assertForbidden();
        }
        foreach (['/admin/rooms/1', '/admin/facilities/1', '/admin/users/1'] as $path) {
            $this->deleteJson($path)->assertForbidden();
        }
    }

    public function test_room_create_edit_photo_and_facilities_are_saved(): void
    {
        Storage::fake('public');
        $facility = DB::table('facilities')->insertGetId(['name' => 'Proyektor']);
        $data = $this->roomData(['facility_ids' => [$facility], 'photo' => UploadedFile::fake()->image('kelas.png')]);
        $this->postJson('/admin/rooms', $data)->assertOk()->assertJsonStructure(['message']);
        $room = DB::table('rooms')->first();
        Storage::disk('public')->assertExists($room->photo_path);
        $this->assertDatabaseHas('facility_room', ['room_id' => $room->id, 'facility_id' => $facility]);

        $this->patchJson('/admin/rooms/'.$room->id, $this->roomData(['name' => 'Ruang diperbarui', 'is_active' => false, 'remove_photo' => true]))->assertOk();
        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'name' => 'Ruang diperbarui', 'is_active' => false, 'photo_path' => null]);
        $this->assertDatabaseMissing('facility_room', ['room_id' => $room->id]);
        Storage::disk('public')->assertMissing($room->photo_path);
        $this->get('/admin/rooms')->assertSeeText('Informasi ruangan berhasil diperbarui.');
    }

    public function test_invalid_room_data_returns_modal_validation_errors_without_writes(): void
    {
        $this->postJson('/admin/rooms', $this->roomData(['capacity' => 0, 'facility_ids' => [999], 'photo' => UploadedFile::fake()->create('bad.svg', 10, 'image/svg+xml')]))
            ->assertUnprocessable()->assertJsonValidationErrors(['capacity', 'facility_ids.0', 'photo']);
        $this->assertDatabaseCount('rooms', 0);
    }

    public function test_room_code_must_be_unique(): void
    {
        $this->room();
        $this->postJson('/admin/rooms', $this->roomData())->assertUnprocessable()->assertJsonValidationErrors('code');
    }

    public function test_room_history_is_preserved_and_capacity_cannot_invalidate_approved_bookings(): void
    {
        $room = $this->room();
        $booking = $this->booking($room, ['status' => 'approved']);
        $this->deleteJson('/admin/rooms/'.$room)->assertUnprocessable();
        $this->patchJson('/admin/rooms/'.$room, $this->roomData(['capacity' => 5]))->assertUnprocessable()->assertJsonValidationErrors('capacity');
        $this->patchJson('/admin/rooms/'.$room, $this->roomData(['is_active' => false]))->assertOk();
        $this->assertDatabaseHas('rooms', ['id' => $room, 'is_active' => false]);
        $this->assertDatabaseHas('bookings', ['id' => $booking, 'status' => 'approved']);
    }

    public function test_unused_room_can_be_deleted(): void
    {
        $room = $this->room();
        $this->deleteJson('/admin/rooms/'.$room)->assertOk();
        $this->assertDatabaseMissing('rooms', ['id' => $room]);
    }

    public function test_failed_room_update_cleans_new_upload_and_preserves_original_photo(): void
    {
        Storage::fake('public');
        $original = UploadedFile::fake()->image('original.png')->store('rooms', 'public');
        $room = $this->room(['photo_path' => $original]);
        $this->booking($room, ['status' => 'approved']);
        $this->patchJson('/admin/rooms/'.$room, $this->roomData(['capacity' => 5, 'photo' => UploadedFile::fake()->image('replacement.png')]))
            ->assertUnprocessable();
        $this->assertDatabaseHas('rooms', ['id' => $room, 'photo_path' => $original, 'capacity' => 30]);
        Storage::disk('public')->assertExists($original);
        $this->assertCount(1, Storage::disk('public')->allFiles('rooms'));
    }

    public function test_facility_management_prevents_deleting_a_used_facility(): void
    {
        $this->postJson('/admin/facilities', ['name' => 'Proyektor'])->assertOk();
        $facility = DB::table('facilities')->value('id');
        $this->patchJson('/admin/facilities/'.$facility, ['name' => 'Proyektor LCD'])->assertOk();
        $room = $this->room();
        DB::table('facility_room')->insert(['room_id' => $room, 'facility_id' => $facility]);
        $this->deleteJson('/admin/facilities/'.$facility)->assertUnprocessable();
        $this->patchJson('/admin/rooms/'.$room, $this->roomData(['facility_ids' => []]))->assertOk();
        $this->deleteJson('/admin/facilities/'.$facility)->assertOk();
        $this->assertDatabaseCount('facilities', 0);
    }

    public function test_admin_creates_users_with_hashed_passwords_and_can_keep_existing_password(): void
    {
        $data = ['name' => 'Dosen Uji', 'identity_number' => '00112233', 'email' => 'dosen@example.com', 'role' => 'dosen', 'password' => 'password-baru', 'password_confirmation' => 'password-baru'];
        $this->postJson('/admin/users', $data)->assertOk();
        $user = User::where('email', 'dosen@example.com')->firstOrFail();
        $hash = $user->password;
        $this->assertTrue(Hash::check('password-baru', $hash));
        $this->assertSame('dosen', $user->role);
        $this->patchJson('/admin/users/'.$user->id, array_replace($data, ['role' => 'mahasiswa', 'password' => '', 'password_confirmation' => '']))->assertOk();
        $this->assertSame($hash, $user->fresh()->password);
        $this->assertSame('mahasiswa', $user->fresh()->role);
        $this->patchJson('/admin/users/'.$user->id, array_replace($data, ['password' => 'password-reset', 'password_confirmation' => 'password-reset']))->assertOk();
        $this->assertTrue(Hash::check('password-reset', $user->fresh()->password));
        $this->deleteJson('/admin/users/'.$user->id)->assertOk();
        $this->assertNull($user->fresh());
    }

    public function test_user_management_validates_identity_email_role_and_password(): void
    {
        $this->postJson('/admin/users', ['name' => 'Uji', 'identity_number' => '', 'email' => $this->admin->email, 'role' => 'root', 'password' => 'abc', 'password_confirmation' => 'xyz'])
            ->assertUnprocessable()->assertJsonValidationErrors(['identity_number', 'email', 'role', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_current_admin_cannot_delete_or_demote_their_own_account(): void
    {
        $this->deleteJson('/admin/users/'.$this->admin->id)->assertUnprocessable();
        $this->patchJson('/admin/users/'.$this->admin->id, ['name' => 'Admin', 'identity_number' => 'ADMIN-01', 'email' => $this->admin->email, 'role' => 'mahasiswa'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->assertSame('admin', $this->admin->fresh()->role);
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_another_admin_can_be_demoted_when_an_admin_remains(): void
    {
        $other = User::factory()->create(['role' => 'admin', 'identity_number' => 'ADMIN-02']);
        $this->patchJson('/admin/users/'.$other->id, ['name' => $other->name, 'identity_number' => $other->identity_number, 'email' => $other->email, 'role' => 'dosen'])->assertOk();
        $this->assertSame('dosen', $other->fresh()->role);
        $this->assertSame('admin', $this->admin->fresh()->role);
    }

    public function test_users_with_booking_or_review_history_cannot_be_deleted(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create(['role' => 'admin']);
        $room = $this->room();
        $this->booking($room, ['user_id' => $owner->id, 'reviewed_by' => $reviewer->id]);
        $this->deleteJson('/admin/users/'.$owner->id)->assertUnprocessable();
        $this->deleteJson('/admin/users/'.$reviewer->id)->assertUnprocessable();
        $this->assertNotNull($owner->fresh());
        $this->assertNotNull($reviewer->fresh());
    }

    public function test_manual_schedule_is_approved_with_trusted_reviewer_and_shows_in_calendar(): void
    {
        $room = $this->room();
        $this->postJson('/admin/schedules', $this->scheduleData($room) + ['status' => 'pending', 'reviewed_by' => 999])->assertOk();
        $this->assertDatabaseHas('bookings', ['room_id' => $room, 'status' => 'approved', 'reviewed_by' => $this->admin->id]);
        $this->get('/admin/rooms?tab=schedules&date=2026-11-01')->assertOk()->assertSeeText('Kegiatan manual');
        $this->get('/dashboard?month=11&year=2026&date=2026-11-01')->assertOk()->assertViewHas('selectedSchedule', fn ($schedule) => $schedule->has($room));
    }

    public function test_schedule_conflicts_are_rejected_but_adjacent_times_are_allowed(): void
    {
        $room = $this->room();
        $this->postJson('/admin/schedules', $this->scheduleData($room))->assertOk();
        $this->postJson('/admin/schedules', $this->scheduleData($room, ['starts_at' => '2026-11-01T10:30', 'ends_at' => '2026-11-01T11:30']))
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->postJson('/admin/schedules', $this->scheduleData($room, ['starts_at' => '2026-11-01T11:00', 'ends_at' => '2026-11-01T12:00']))->assertOk();
        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_schedule_requires_an_active_room_valid_interval_and_capacity(): void
    {
        $inactive = $this->room(['is_active' => false]);
        $this->postJson('/admin/schedules', $this->scheduleData($inactive))->assertUnprocessable()->assertJsonValidationErrors('room_id');
        $room = $this->room(['code' => 'R-02']);
        $this->postJson('/admin/schedules', $this->scheduleData($room, ['participant_count' => 31]))->assertUnprocessable()->assertJsonValidationErrors('participant_count');
        $this->postJson('/admin/schedules', $this->scheduleData($room, ['ends_at' => '2026-11-01T09:00']))->assertUnprocessable()->assertJsonValidationErrors('ends_at');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_schedule_with_explicit_timezone_is_normalized_before_conflict_checks(): void
    {
        $room = $this->room();
        $this->postJson('/admin/schedules', $this->scheduleData($room, ['starts_at' => '2026-11-01T03:00:00Z', 'ends_at' => '2026-11-01T04:00:00Z']))->assertOk();
        $this->assertDatabaseHas('bookings', ['room_id' => $room, 'starts_at' => '2026-11-01 10:00:00', 'ends_at' => '2026-11-01 11:00:00']);
        $this->postJson('/admin/schedules', $this->scheduleData($room, ['starts_at' => '2026-11-01T10:30', 'ends_at' => '2026-11-01T11:30']))
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
    }

    public function test_approved_schedule_can_be_edited_and_cancelled_without_deleting_history(): void
    {
        $room = $this->room();
        $this->postJson('/admin/schedules', $this->scheduleData($room))->assertOk();
        $id = DB::table('bookings')->value('id');
        $this->patchJson('/admin/schedules/'.$id, $this->scheduleData($room, ['purpose' => 'Jadwal diubah']))->assertOk();
        $this->patchJson('/admin/schedules/'.$id.'/cancel')->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $id, 'purpose' => 'Jadwal diubah', 'status' => 'cancelled']);
        $this->patchJson('/admin/schedules/'.$id.'/cancel')->assertUnprocessable();
        $this->patchJson('/admin/schedules/'.$id, $this->scheduleData($room))->assertUnprocessable();
    }

    public function test_moving_a_schedule_to_another_room_rechecks_conflicts(): void
    {
        $first = $this->room();
        $second = $this->room(['code' => 'R-02']);
        $this->postJson('/admin/schedules', $this->scheduleData($first))->assertOk();
        $id = DB::table('bookings')->where('room_id', $first)->value('id');
        $this->postJson('/admin/schedules', $this->scheduleData($second))->assertOk();
        $this->patchJson('/admin/schedules/'.$id, $this->scheduleData($second))->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertDatabaseHas('bookings', ['id' => $id, 'room_id' => $first]);
        $this->patchJson('/admin/schedules/'.$id, $this->scheduleData($second, ['starts_at' => '2026-11-01T11:00', 'ends_at' => '2026-11-01T12:00']))->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $id, 'room_id' => $second]);
    }

    public function test_admin_can_approve_a_pending_application_only_once(): void
    {
        $room = $this->room();
        $id = $this->booking($room);
        $this->patchJson('/admin/bookings/'.$id.'/review', ['decision' => 'approved'])->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $id, 'status' => 'approved', 'reviewed_by' => $this->admin->id]);
        $this->patchJson('/admin/bookings/'.$id.'/review', ['decision' => 'rejected', 'rejection_reason' => 'Ubah keputusan'])->assertUnprocessable();
        $this->assertDatabaseHas('bookings', ['id' => $id, 'status' => 'approved']);
    }

    public function test_rejection_requires_a_reason_and_records_the_actual_admin(): void
    {
        $id = $this->booking($this->room());
        $this->patchJson('/admin/bookings/'.$id.'/review', ['decision' => 'rejected'])->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');
        $this->patchJson('/admin/bookings/'.$id.'/review', ['decision' => 'rejected', 'rejection_reason' => 'Kegiatan memerlukan ruangan lain', 'reviewed_by' => 999])->assertOk();
        $this->assertDatabaseHas('bookings', ['id' => $id, 'status' => 'rejected', 'reviewed_by' => $this->admin->id, 'rejection_reason' => 'Kegiatan memerlukan ruangan lain']);
    }

    public function test_approval_rechecks_overlap_capacity_and_room_status(): void
    {
        $room = $this->room();
        $first = $this->booking($room);
        $second = $this->booking($room);
        $this->patchJson('/admin/bookings/'.$first.'/review', ['decision' => 'approved'])->assertOk();
        $this->patchJson('/admin/bookings/'.$second.'/review', ['decision' => 'approved'])->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertDatabaseHas('bookings', ['id' => $second, 'status' => 'pending']);

        $otherRoom = $this->room(['code' => 'R-02']);
        $capacity = $this->booking($otherRoom, ['participant_count' => 40]);
        $this->patchJson('/admin/bookings/'.$capacity.'/review', ['decision' => 'approved'])->assertUnprocessable()->assertJsonValidationErrors('participant_count');
        DB::table('rooms')->where('id', $otherRoom)->update(['is_active' => false]);
        $this->patchJson('/admin/bookings/'.$capacity.'/review', ['decision' => 'approved'])->assertUnprocessable()->assertJsonValidationErrors('room_id');
    }

    public function test_missing_records_return_404(): void
    {
        $this->deleteJson('/admin/rooms/999')->assertNotFound();
        $this->deleteJson('/admin/facilities/999')->assertNotFound();
        $this->deleteJson('/admin/users/999')->assertNotFound();
        $this->patchJson('/admin/bookings/999/review', ['decision' => 'approved'])->assertNotFound();
    }

    private function roomData(array $overrides = []): array
    {
        return array_replace(['code' => 'R-01', 'name' => 'Ruang Uji', 'capacity' => 30, 'location' => 'Gedung FMIPA', 'description' => 'Informasi ruangan', 'is_active' => true], $overrides);
    }

    private function room(array $overrides = []): int
    {
        return DB::table('rooms')->insertGetId($this->roomData($overrides) + ['created_at' => now(), 'updated_at' => now()]);
    }

    private function booking(int $room, array $overrides = []): int
    {
        return DB::table('bookings')->insertGetId(array_replace([
            'room_id' => $room, 'user_id' => $this->admin->id, 'purpose' => 'Pengajuan uji', 'participant_count' => 20,
            'starts_at' => now()->addDay()->startOfDay()->addHours(8), 'ends_at' => now()->addDay()->startOfDay()->addHours(9),
            'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
        ], $overrides));
    }

    private function scheduleData(int $room, array $overrides = []): array
    {
        return array_replace(['room_id' => $room, 'user_id' => $this->admin->id, 'purpose' => 'Kegiatan manual', 'participant_count' => 20, 'starts_at' => '2026-11-01T10:00', 'ends_at' => '2026-11-01T11:00'], $overrides);
    }

    public static function nonAdminRoles(): array
    {
        return ['mahasiswa' => ['mahasiswa'], 'dosen' => ['dosen']];
    }
}
