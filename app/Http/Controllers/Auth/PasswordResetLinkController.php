<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Menyajikan formulir dan mengirim tautan pemulihan kata sandi.
 * Konsep Laravel: Password Reset, Password Broker dan Notifications.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Menampilkan formulir permintaan tautan reset kata sandi.
     * Konsep Laravel: Password Reset, Password Broker dan Notifications.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Memvalidasi email dan meminta Password Broker mengirim tautan pemulihan.
     * Konsep Laravel: Password Reset, Password Broker dan Notifications.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
