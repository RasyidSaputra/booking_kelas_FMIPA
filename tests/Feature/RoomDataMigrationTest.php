<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoomDataMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_adds_fifteen_unique_rooms_and_can_run_again(): void
    {
        $this->assertDatabaseCount('rooms', 15);
        $this->assertSame(15, DB::table('rooms')->distinct()->count('name'));
        $this->assertSame(14, DB::table('rooms')->where('name', 'like', 'RUANG ___')->count());
        $this->assertDatabaseHas('rooms', ['name' => 'AUDITORIUM GEDUNG PROF. DR. DADANG ISKANDAR']);
        $this->assertSame(0, DB::table('rooms')->where('is_active', true)->count());

        $migration = require database_path('migrations/2026_10_29_000000_insert_unique_rooms.php');
        $migration->up();

        $this->assertDatabaseCount('rooms', 15);
    }

    public function test_existing_rooms_are_preserved_and_matched_by_code_or_normalized_name(): void
    {
        DB::table('rooms')->delete();
        $existing = [
            ['code' => 'FMIPA-101', 'name' => 'Ruang Kuliah 101'],
            ['code' => 'RUANG-LAMA', 'name' => '  ruang   202  '],
        ];

        foreach ($existing as $room) {
            DB::table('rooms')->insert($room + [
                'capacity' => 50,
                'location' => 'Lokasi terverifikasi',
                'is_active' => true,
            ]);
        }

        $migration = require database_path('migrations/2026_10_29_000000_insert_unique_rooms.php');
        $migration->up();

        $this->assertDatabaseCount('rooms', 15);
        foreach ($existing as $room) {
            $this->assertDatabaseHas('rooms', $room + [
                'capacity' => 50,
                'location' => 'Lokasi terverifikasi',
                'is_active' => true,
            ]);
        }
        $this->assertDatabaseMissing('rooms', ['code' => 'FMIPA-202']);

        $migration->down();
        $this->assertDatabaseCount('rooms', 15);
    }
}
