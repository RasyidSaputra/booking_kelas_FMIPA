<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'identity_number', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $oldAvatar = null;

        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar')->store('avatars', 'public');

            if (! $newAvatar) {
                throw ValidationException::withMessages(['avatar' => 'Foto profil belum dapat disimpan. Coba kembali.']);
            }

            $oldAvatar = $user->avatar_path;
            $user->avatar_path = $newAvatar;
        } elseif ($request->boolean('remove_avatar')) {
            $oldAvatar = $user->avatar_path;
            $user->avatar_path = null;
        }

        try {
            $user->save();
        } catch (\Throwable $e) {
            if (isset($newAvatar) && $newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $e;
        }

        if ($oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'string', 'current_password'],
        ], [
            'password.required' => 'Kata sandi wajib diisi untuk menghapus akun.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.current_password' => 'Kata sandi saat ini tidak sesuai.',
        ]);

        $user = $request->user();

        if (DB::table('bookings')->where('user_id', $user->id)->orWhere('reviewed_by', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'password' => 'Akun memiliki riwayat peminjaman atau pemeriksaan dan tidak dapat dihapus.',
            ])->errorBag('userDeletion');
        }

        Auth::logout();

        $avatar = $user->avatar_path;
        $user->delete();

        if ($avatar) {
            Storage::disk('public')->delete($avatar);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
