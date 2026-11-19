<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Mengelola login dan logout pengguna melalui sesi browser.
 * Konsep Laravel: Authentication, Session dan Controller Laravel Breeze.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan formulir login Blade.
     * Konsep Laravel: Authentication, Session dan Controller Laravel Breeze.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Mengautentikasi kredensial, meregenerasi session, dan mengarahkan ke halaman tujuan atau dashboard.
     * Konsep Laravel: Authentication, Session dan Controller Laravel Breeze.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Mengakhiri login web, membatalkan session, dan meregenerasi token CSRF.
     * Konsep Laravel: Authentication, Session dan Controller Laravel Breeze.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
