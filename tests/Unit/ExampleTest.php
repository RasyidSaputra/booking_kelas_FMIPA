<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Mengelompokkan pengujian Example pada aplikasi.
 * Konsep Laravel: Unit Test (PHPUnit).
 */
class ExampleTest extends TestCase
{
    /**
     * Memastikan assertion boolean paling dasar pada PHPUnit berjalan.
     * Konsep Laravel: Unit Test (PHPUnit).
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
