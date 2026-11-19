<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Menandai email pengguna sebagai terverifikasi melalui permintaan verifikasi.
 * Konsep Laravel: Invokable Controller, Email Verification dan Events.
 */
class VerifyEmailController extends Controller
{
    /**
     * Menandai email yang belum diverifikasi dan memicu event Verified sebelum redirect ke dashboard.
     * Konsep Laravel: Invokable Controller, Email Verification dan Events.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
