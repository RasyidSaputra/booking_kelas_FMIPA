<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Meminta konfirmasi kata sandi sebelum pengguna meneruskan aksi sensitif.
 * Konsep Laravel: Password Confirmation, Authentication dan Session.
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Menampilkan formulir konfirmasi kata sandi.
     * Konsep Laravel: Password Confirmation, Authentication dan Session.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Memeriksa kata sandi pengguna lalu menyimpan waktu konfirmasi di session.
     * Konsep Laravel: Password Confirmation, Authentication dan Session.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
