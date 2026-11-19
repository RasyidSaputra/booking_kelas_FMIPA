<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Mengelompokkan pengujian Booking Authorization pada aplikasi.
 * Konsep Laravel: Feature Test, HTTP Testing dan Database Testing.
 */
class BookingAuthorizationTest extends TestCase
{
    /**
     * Memastikan halaman awal dapat diakses; ini tes dasar, belum menguji seluruh fitur sesuai nama class.
     * Konsep Laravel: Feature Test, HTTP Testing dan Database Testing.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
