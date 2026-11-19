<?php

namespace Database\Factories;

use App\Models\room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Mendefinisikan factory data Room untuk seeding atau pengujian.
 * Konsep Laravel: Eloquent Model Factory dan Faker.
 *
 * @extends Factory<room>
 */
class RoomFactory extends Factory
{
    /**
     * Menyediakan kerangka atribut default Room; saat ini belum mengisi data factory.
     * Konsep Laravel: Eloquent Model Factory, definition().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
