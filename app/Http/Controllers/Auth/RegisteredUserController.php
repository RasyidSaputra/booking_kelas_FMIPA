<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Mendaftarkan akun mahasiswa dan membuat sesi login setelah pendaftaran.
 * Konsep Laravel: Registration, Eloquent, Authentication dan Events.
 */
class RegisteredUserController extends Controller
{
    /**
     * Menampilkan formulir pendaftaran akun.
     * Konsep Laravel: Registration, Eloquent, Authentication dan Events.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Membuat akun mahasiswa, memicu event Registered, dan membuat sesi login pengguna baru.
     * Konsep Laravel: Registration, Eloquent, Authentication dan Events.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User([
            'name' => $validated['name'],
            'identity_number' => $validated['identity_number'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Registrasi publik selalu menjadi mahasiswa, terlepas dari input role.
        $user->role = 'mahasiswa';
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
