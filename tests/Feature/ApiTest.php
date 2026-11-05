<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private User $borrower;

    private int $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 5)->setTime(8, 0));
        $this->borrower = User::factory()->create(['identity_number' => '0012345']);
        $this->room = DB::table('rooms')->insertGetId([
            'code' => 'API-101', 'name' => 'Ruang API', 'location' => 'Lantai 1',
            'capacity' => 40, 'is_active' => true,
        ]);
    }

    private function token(?User $user = null, $expiresAt = null): string
    {
        return ($user ?? $this->borrower)->createToken('Pengujian', ['*'], $expiresAt)->plainTextToken;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'room_id' => $this->room, 'booking_date' => '2026-11-07',
            'start_time' => '09:00', 'end_time' => '10:00',
            'purpose' => 'Kegiatan mahasiswa', 'participant_count' => 25,
        ], $overrides);
    }

    public function test_login_me_and_logout_revoke_only_current_token(): void
    {
        $otherToken = $this->token();
        $response = $this->postJson('/api/v1/login', [
            'identity_number' => '0012345', 'password' => 'password', 'device_name' => 'Postman',
        ])->assertOk()->assertJsonPath('data.id', $this->borrower->id)->assertJsonPath('token_type', 'Bearer')
            ->assertJsonMissingPath('data.password');
        $token = $response->json('token');
        $this->assertDatabaseHas('personal_access_tokens', ['token' => hash('sha256', explode('|', $token, 2)[1])]);
        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.identity_number', '0012345');
        $this->withToken($token)->postJson('/api/v1/logout')->assertNoContent();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($otherToken)->getJson('/api/v1/me')->assertOk();
    }

    public function test_invalid_login_and_rate_limit_do_not_issue_tokens(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', ['identity_number' => '0012345', 'password' => 'wrong', 'device_name' => 'Test'])
                ->assertUnprocessable()->assertJsonValidationErrors('identity_number');
        }
        $this->postJson('/api/v1/login', ['identity_number' => '0012345', 'password' => 'password', 'device_name' => 'Test'])->assertTooManyRequests();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_always_returns_json_for_guests_validation_and_missing_routes(): void
    {
        $this->get('/api/v1/rooms')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
        $this->post('/api/v1/login', [])->assertUnprocessable()->assertJsonValidationErrors(['identity_number', 'password', 'device_name']);
        $this->get('/api/v1/missing')->assertNotFound()->assertHeader('Content-Type', 'application/json');
    }

    public function test_expired_token_and_web_session_cannot_authenticate_api(): void
    {
        $token = $this->token(expiresAt: now()->subMinute());
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->borrower, 'web')->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_rooms_filter_pagination_and_inactive_room_protection(): void
    {
        $this->withToken($this->token())->getJson('/api/v1/rooms?q=API&capacity=30&per_page=1')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $this->room)->assertJsonStructure(['links', 'meta']);
        $this->getJson('/api/v1/rooms/'.$this->room)->assertOk();
        DB::table('rooms')->where('id', $this->room)->update(['is_active' => false]);
        $this->getJson('/api/v1/rooms/'.$this->room)->assertNotFound();
        $this->getJson('/api/v1/rooms?per_page=101')->assertUnprocessable();
    }

    public function test_weekend_booking_uses_authenticated_owner_and_requires_admin_review(): void
    {
        $this->withToken($this->token());
        $id = $this->postJson('/api/v1/bookings', $this->payload(['user_id' => 999, 'status' => 'approved']))
            ->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.user_id', $this->borrower->id)->json('data.id');
        $this->getJson('/api/v1/bookings')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/bookings/'.$id)->assertOk();
        $this->patchJson('/api/v1/admin/bookings/'.$id.'/review', ['decision' => 'approved'])->assertForbidden();
        $this->getJson('/api/v1/admin/bookings')->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->withToken($this->token(User::factory()->create()))->getJson('/api/v1/bookings/'.$id)->assertForbidden();
        $this->getJson('/api/v1/bookings')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_booking_validates_capacity_time_and_inactive_rooms(): void
    {
        $this->withToken($this->token());
        foreach ([['participant_count' => 41], ['start_time' => '07:00'], ['end_time' => '19:00'], ['end_time' => '08:00'], ['booking_date' => '2026-11-04'], ['purpose' => '']] as $invalid) {
            $this->postJson('/api/v1/bookings', $this->payload($invalid))->assertUnprocessable();
        }
        $this->postJson('/api/v1/bookings', $this->payload(['booking_date' => '2026-11-05', 'start_time' => '07:30']))->assertUnprocessable();
        DB::table('rooms')->where('id', $this->room)->update(['is_active' => false]);
        $this->postJson('/api/v1/bookings', $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_admin_approval_rechecks_conflicts_and_adjacent_times_are_allowed(): void
    {
        $this->withToken($this->token());
        $first = $this->postJson('/api/v1/bookings', $this->payload())->assertCreated()->json('data.id');
        $second = $this->postJson('/api/v1/bookings', $this->payload())->assertCreated()->json('data.id');
        $this->app['auth']->forgetGuards();
        $this->withToken($this->token(User::factory()->create(['role' => 'admin'])));
        $this->getJson('/api/v1/admin/bookings?status=pending')->assertOk()->assertJsonCount(2, 'data');
        $this->patchJson('/api/v1/admin/bookings/'.$first.'/review', ['decision' => 'approved'])->assertOk()->assertJsonPath('data.status', 'approved');
        $this->patchJson('/api/v1/admin/bookings/'.$second.'/review', ['decision' => 'approved'])->assertUnprocessable();
        $this->patchJson('/api/v1/admin/bookings/'.$first.'/review', ['decision' => 'approved'])->assertUnprocessable();
        $this->postJson('/api/v1/bookings', $this->payload())->assertUnprocessable();
        $this->getJson('/api/v1/rooms/'.$this->room.'/availability?date=2026-11-07&start_time=09:00&end_time=10:00')->assertOk()->assertJsonPath('data.available', false);
        $this->getJson('/api/v1/rooms/'.$this->room.'/availability?date=2026-11-07&start_time=10:00&end_time=11:00')->assertOk()->assertJsonPath('data.available', true);
        $this->postJson('/api/v1/bookings', $this->payload(['start_time' => '10:00', 'end_time' => '11:00']))->assertCreated();
        $this->assertDatabaseHas('bookings', ['id' => $second, 'status' => 'pending']);
    }

    public function test_admin_rejection_requires_reason_and_cannot_be_repeated(): void
    {
        $this->withToken($this->token());
        $id = $this->postJson('/api/v1/bookings', $this->payload())->assertCreated()->json('data.id');
        $this->app['auth']->forgetGuards();
        $this->withToken($this->token(User::factory()->create(['role' => 'admin'])));
        $this->patchJson('/api/v1/admin/bookings/'.$id.'/review', ['decision' => 'rejected'])->assertUnprocessable();
        $this->patchJson('/api/v1/admin/bookings/'.$id.'/review', ['decision' => 'rejected', 'rejection_reason' => 'Surat belum sesuai.'])
            ->assertOk()->assertJsonPath('data.rejection_reason', 'Surat belum sesuai.');
        $this->patchJson('/api/v1/admin/bookings/'.$id.'/review', ['decision' => 'approved'])->assertUnprocessable();
    }

    public function test_web_and_api_share_conflict_checks(): void
    {
        $this->actingAs($this->borrower)->post('/bookings', $this->payload())->assertSessionHasNoErrors()->assertRedirect('/bookings');
        $booking = DB::table('bookings')->first();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch('/admin/bookings/'.$booking->id.'/review', ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->app['auth']->forgetGuards();
        $this->withToken($this->token())->postJson('/api/v1/bookings', $this->payload())->assertUnprocessable();
        $this->actingAs($this->borrower)->from('/rooms/'.$this->room)->post('/bookings', $this->payload())->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_approval_rechecks_room_capacity_and_active_state(): void
    {
        $this->withToken($this->token());
        $id = $this->postJson('/api/v1/bookings', $this->payload())->assertCreated()->json('data.id');
        $this->app['auth']->forgetGuards();
        $this->withToken($this->token(User::factory()->create(['role' => 'admin'])));
        DB::table('rooms')->where('id', $this->room)->update(['capacity' => 10]);
        $this->patchJson('/api/v1/admin/bookings/'.$id.'/review', ['decision' => 'approved'])->assertUnprocessable();
        DB::table('rooms')->where('id', $this->room)->update(['capacity' => 40, 'is_active' => false]);
        $this->patchJson('/api/v1/admin/bookings/'.$id.'/review', ['decision' => 'approved'])->assertUnprocessable();
        $this->assertDatabaseHas('bookings', ['id' => $id, 'status' => 'pending', 'reviewed_by' => null]);
    }

    public function test_missing_records_and_invalid_availability_return_expected_errors(): void
    {
        $this->withToken($this->token());
        $this->getJson('/api/v1/bookings/999999')->assertNotFound();
        $this->getJson('/api/v1/rooms/999999')->assertNotFound();
        $this->getJson('/api/v1/rooms/'.$this->room.'/availability')->assertUnprocessable();
        $this->getJson('/api/v1/rooms/'.$this->room.'/availability?date=2026-11-07&start_time=11:00&end_time=10:00')->assertUnprocessable();
        $this->getJson('/api/v1/bookings?status=invalid')->assertUnprocessable();
        $this->postJson('/api/v1/bookings', $this->payload(['booking_date' => 'tomorrow']))->assertUnprocessable();
    }

    public function test_malformed_or_unknown_login_never_issues_token(): void
    {
        $this->postJson('/api/v1/login', ['identity_number' => ['bad'], 'password' => 'password', 'device_name' => 'Test'])->assertUnprocessable();
        $this->postJson('/api/v1/login', ['identity_number' => 'missing', 'password' => 'password', 'device_name' => 'Test'])->assertUnprocessable();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
