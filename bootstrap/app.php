<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    /**
     * Menyediakan titik konfigurasi middleware tambahan; saat ini belum ada pengaturan khusus.
     * Konsep Laravel: Application Bootstrap dan Middleware Configuration.
     */
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    /**
     * Mengonfigurasi rendering exception agar request API atau request yang meminta JSON mendapat respons JSON.
     * Konsep Laravel: Application Bootstrap dan Exception Handling.
     */
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request, $exception) => $request->is('api/*') || $request->expectsJson());
    })->create();
