<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Mengirim ulang pemberitahuan verifikasi alamat email pengguna.
 * Konsep Laravel: Email Verification, Notifications dan Controller.
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Mengirim ulang notifikasi verifikasi jika email pengguna belum terverifikasi.
     * Konsep Laravel: Email Verification, Notifications dan Controller.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
