<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Menjalankan seeder sesuai urutan agar data yang saling bergantung tersedia.
 * Konsep Laravel: Database Seeder, Seeder Orchestration.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Menjalankan seeder sesuai urutan agar data yang saling bergantung tersedia.
     * Konsep Laravel: Database Seeder, Seeder Orchestration.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoomSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
