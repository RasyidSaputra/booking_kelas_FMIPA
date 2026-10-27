<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $capacities = [40, 45, 50, 50, 60, 45, 40, 50, 60];

        // Lantai 1 sampai Lantai 3
        for ($floor = 1; $floor <= 3; $floor++) {
            // Ruangan 1 sampai Ruangan 9 per lantai (101-109, 201-209, 301-309)
            for ($roomNum = 1; $roomNum <= 9; $roomNum++) {
                $numberStr = sprintf('%d%02d', $floor, $roomNum); // Contoh: 101, 205, 309
                $code = "FMIPA-{$numberStr}";
                $name = "Ruang Kuliah {$numberStr}";
                $capacity = $capacities[($roomNum - 1) % count($capacities)];
                $location = "Gedung Kuliah Lantai {$floor}";
                $description = "Ruang perkuliahan dan presentasi berpendingin udara di Gedung Kuliah Lantai {$floor}, FMIPA Universitas Riau.";

                DB::table('rooms')->updateOrInsert(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'capacity' => $capacity,
                        'location' => $location,
                        'description' => $description,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
