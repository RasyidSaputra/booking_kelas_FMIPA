<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Memperbarui kata sandi pengguna setelah kata sandi saat ini diperiksa.
 * Konsep Laravel: Authentication, Validation Error Bag dan Hashing.
 */
class PasswordController extends Controller
{
    /**
     * Memvalidasi kata sandi saat ini dan konfirmasi sebelum menyimpan hash kata sandi baru.
     * Konsep Laravel: Authentication, Validation Error Bag dan Hashing.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ], [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi kata sandi baru belum cocok.',
            'password.min' => 'Kata sandi baru minimal :min karakter.',
        ], [
            'current_password' => 'Kata sandi saat ini',
            'password' => 'Kata sandi baru',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
