<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mengelompokkan pengujian Example pada aplikasi.
 * Konsep Laravel: Feature Test, HTTP Testing dan Database Testing.
 */
class ExampleTest extends TestCase
{
    /**
     * Memastikan aplikasi mengembalikan respons HTTP yang berhasil.
     * Konsep Laravel: Feature Test, HTTP Testing dan Database Testing.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
