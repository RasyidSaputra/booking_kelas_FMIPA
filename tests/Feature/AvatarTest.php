<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        Storage::fake('public');
        $owner = User::factory()->create(['identity_number' => 'AVATAR-001']);
        $owner->avatar_path = 'avatars/original.png';
        $owner->save();
        Storage::disk('public')->put($owner->avatar_path, 'test image');

        return $owner;
    }

    private function data(User $owner, array $extra = []): array
    {
        return $extra + ['name' => $owner->name, 'email' => $owner->email, 'identity_number' => $owner->identity_number];
    }

    public function test_avatar_url_follows_the_public_disk_configuration(): void
    {
        config(['filesystems.disks.public.url' => 'https://assets.example.test/kelasku/storage']);
        $owner = User::factory()->make();
        $this->assertNull($owner->avatarUrl());
        $owner->avatar_path = 'avatars/photo.png';

        $this->assertSame('https://assets.example.test/kelasku/storage/avatars/photo.png', $owner->avatarUrl());
    }

    public function test_admin_deleting_an_account_removes_its_avatar(): void
    {
        $owner = $this->owner();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->deleteJson('/admin/users/'.$owner->id)->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        Storage::disk('public')->assertMissing($owner->avatar_path);
    }

    public function test_rejected_account_deletion_keeps_its_avatar(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner)->deleteJson('/admin/users/'.$owner->id)->assertForbidden();
        $this->actingAs($owner)->delete('/profile', ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertDatabaseHas('users', ['id' => $owner->id, 'avatar_path' => $owner->avatar_path]);
        Storage::disk('public')->assertExists($owner->avatar_path);
    }

    public function test_stale_profile_requests_clean_up_the_latest_replaced_avatar(): void
    {
        $owner = $this->owner();
        $stale = $owner->fresh();
        $this->actingAs($owner)->patch('/profile', $this->data($owner, [
            'avatar' => UploadedFile::fake()->image('first.png'),
        ]))->assertSessionHasNoErrors()->assertRedirect('/profile');
        $firstAvatar = $owner->fresh()->avatar_path;

        $this->actingAs($stale)->patch('/profile', $this->data($stale, [
            'avatar' => UploadedFile::fake()->image('second.png'),
        ]))->assertSessionHasNoErrors()->assertRedirect('/profile');

        Storage::disk('public')->assertMissing($firstAvatar);
        Storage::disk('public')->assertExists($owner->fresh()->avatar_path);
        $this->assertCount(1, Storage::disk('public')->allFiles('avatars'));
    }

    public function test_stale_remove_request_deletes_the_current_avatar(): void
    {
        $owner = $this->owner();
        $stale = $owner->fresh();
        $this->actingAs($owner)->patch('/profile', $this->data($owner, [
            'avatar' => UploadedFile::fake()->image('replacement.png'),
        ]))->assertSessionHasNoErrors();

        $this->actingAs($stale)->patch('/profile', $this->data($stale, ['remove_avatar' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertNull($owner->fresh()->avatar_path);
        $this->assertSame([], Storage::disk('public')->allFiles('avatars'));
    }

    public function test_self_deletion_uses_the_latest_avatar(): void
    {
        $owner = $this->owner();
        $stale = $owner->fresh();
        $this->actingAs($owner)->patch('/profile', $this->data($owner, [
            'avatar' => UploadedFile::fake()->image('replacement.png'),
        ]))->assertSessionHasNoErrors();

        $this->actingAs($stale)->delete('/profile', ['password' => 'password'])->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertSame([], Storage::disk('public')->allFiles('avatars'));
    }

    public function test_failed_database_save_keeps_the_original_avatar_and_cleans_the_new_upload(): void
    {
        $owner = $this->owner();
        $this->withoutExceptionHandling();
        $event = 'eloquent.saving: '.User::class;
        Event::listen($event, function (): void {
            throw new RuntimeException('Simulated save failure');
        });

        try {
            $this->actingAs($owner)->patch('/profile', $this->data($owner, [
                'avatar' => UploadedFile::fake()->image('replacement.png'),
            ]));
            $this->fail('The database save should have failed.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated save failure', $exception->getMessage());
        } finally {
            Event::forget($event);
        }

        $this->assertSame('avatars/original.png', $owner->fresh()->avatar_path);
        $this->assertSame(['avatars/original.png'], Storage::disk('public')->allFiles('avatars'));
    }

    public function test_rollback_of_admin_deletion_does_not_delete_the_avatar_file(): void
    {
        $owner = $this->owner();
        $admin = User::factory()->create(['role' => 'admin']);
        DB::beginTransaction();
        try {
            $this->actingAs($admin)->deleteJson('/admin/users/'.$owner->id)->assertOk();
            Storage::disk('public')->assertExists($owner->avatar_path);
        } finally {
            DB::rollBack();
        }

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
        Storage::disk('public')->assertExists($owner->avatar_path);
    }
}
