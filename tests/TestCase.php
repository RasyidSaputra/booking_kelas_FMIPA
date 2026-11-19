<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

/**
 * Menjadi dasar pengujian dengan pengaman database SQLite dalam memori.
 * Konsep Laravel: Laravel Testing, Test Lifecycle dan Test Environment.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Menyiapkan aplikasi pengujian dan menghentikan tes jika database bukan SQLite dalam memori.
     * Konsep Laravel: Laravel Testing, Test Lifecycle dan Test Environment.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        // Periksa sebelum RefreshDatabase menjalankan migration pengujian.
        if (! $app->environment('testing')
            || $app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:'
            || ! empty($app['config']->get('database.connections.sqlite.url'))) {
            throw new RuntimeException('Test dihentikan: pengujian harus memakai SQLite :memory: pada environment testing.');
        }

        return $app;
    }
}
