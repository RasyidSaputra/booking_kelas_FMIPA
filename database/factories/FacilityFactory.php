<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Mendefinisikan factory data Facility untuk seeding atau pengujian.
 * Konsep Laravel: Eloquent Model Factory dan Faker.
 *
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * Menyediakan kerangka atribut default Facility; saat ini belum mengisi data factory.
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
