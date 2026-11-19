<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Mendefinisikan factory data Booking untuk seeding atau pengujian.
 * Konsep Laravel: Eloquent Model Factory dan Faker.
 *
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Menyediakan kerangka atribut default Booking; saat ini belum mengisi data factory.
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
