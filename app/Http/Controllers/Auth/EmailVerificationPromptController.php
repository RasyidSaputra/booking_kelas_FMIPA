<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menampilkan pemberitahuan verifikasi atau mengarahkan pengguna yang sudah terverifikasi.
 * Konsep Laravel: Invokable Controller dan Email Verification.
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Mengarahkan akun terverifikasi ke dashboard atau menampilkan halaman verifikasi email.
     * Konsep Laravel: Invokable Controller dan Email Verification.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
