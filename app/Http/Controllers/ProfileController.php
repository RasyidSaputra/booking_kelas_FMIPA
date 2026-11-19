<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Mengelola profil, avatar, serta penghapusan akun milik pengguna yang masuk.
 * Konsep Laravel: Controller (MVC), Authentication, Eloquent dan Filesystem.
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan formulir profil untuk akun yang sedang terautentikasi.
     * Konsep Laravel: Controller (MVC), Authentication, Eloquent dan Filesystem.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Memperbarui kolom profil yang diizinkan dan mengganti avatar dengan transaksi serta pembersihan berkas lama.
     * Konsep Laravel: Controller (MVC), Authentication, Eloquent dan Filesystem.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $newAvatar = null;

        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar')->store('avatars', 'public');

            if (! $newAvatar) {
                throw ValidationException::withMessages(['avatar' => 'Foto profil belum dapat disimpan. Coba kembali.']);
            }
        }

        try {
            $oldAvatar = DB::transaction(function () use ($request, $newAvatar): ?string {
                // Baca ulang setelah lock agar request bersamaan memakai avatar terbaru.
                $user = User::whereKey($request->user()->getAuthIdentifier())->lockForUpdate()->firstOrFail();
                $user->fill($request->safe()->only(['name', 'identity_number', 'email']));
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                $oldAvatar = null;
                if ($newAvatar !== null || $request->boolean('remove_avatar')) {
                    $oldAvatar = $user->avatar_path;
                    $user->avatar_path = $newAvatar;
                }
                $user->save();

                return $oldAvatar;
            }, 3);
        } catch (\Throwable $e) {
            if ($newAvatar !== null) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $e;
        }

        // Sinkronkan akun yang sudah di-cache oleh guard dengan hasil penyimpanan.
        $request->user()->refresh();

        if ($oldAvatar) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($oldAvatar));
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Memeriksa kata sandi dan riwayat sebelum menghapus akun, membersihkan avatar, serta mengakhiri sesi.
     * Konsep Laravel: Controller (MVC), Authentication, Eloquent dan Filesystem.
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

        $avatar = DB::transaction(function () use ($request): ?string {
            $user = User::whereKey($request->user()->getAuthIdentifier())->lockForUpdate()->firstOrFail();
            if (DB::table('bookings')->where('user_id', $user->id)->orWhere('reviewed_by', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'password' => 'Akun memiliki riwayat peminjaman atau pemeriksaan dan tidak dapat dihapus.',
                ])->errorBag('userDeletion');
            }
            $avatar = $user->avatar_path;
            $user->delete();

            return $avatar;
        }, 3);

        Auth::logout();

        if ($avatar) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($avatar));
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
