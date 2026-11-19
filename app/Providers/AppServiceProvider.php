<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Mendaftarkan Gate administrator dan pembatasan laju permintaan API saat aplikasi dimulai.
 * Konsep Laravel: Service Provider, Gate Authorization dan Rate Limiting.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Menyediakan kerangka aksi register; method ini belum berisi implementasi.
     * Konsep Laravel: Service Provider, Gate Authorization dan Rate Limiting.
     */
    public function register(): void
    {
        //
    }

    /**
     * Mendefinisikan Gate view-room-master dan batas permintaan login maupun endpoint API.
     * Konsep Laravel: Service Provider, Gate Authorization dan Rate Limiting.
     */
    public function boot(): void
    {
        Gate::define('view-room-master', fn (User $user): bool => $user->role === 'admin');
        RateLimiter::for('api-login', fn (Request $request) => [
            Limit::perMinute(20)->by('ip:'.$request->ip()),
            Limit::perMinute(5)->by('identity:'.$request->ip().'|'.hash('sha256', json_encode($request->input('identity_number')))),
        ]);
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by((string) ($request->user()?->id ?? $request->ip())));
    }
}
