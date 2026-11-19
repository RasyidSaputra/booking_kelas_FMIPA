<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/**
 * Mendaftarkan perintah console contoh yang mencetak kutipan inspiratif.
 * Konsep Laravel: Artisan Console Command dan Command Closure.
 */
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
