<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RespondsWithMasterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Mengelola akun pengguna sambil menjaga admin terakhir dan riwayat peminjaman.
 * Konsep Laravel: Controller (MVC), Eloquent, Hashing dan Database Transaction.
 */
class UserManagementController extends Controller
{
    use RespondsWithMasterData;

    /**
     * Membuat akun dari data tervalidasi dan menyimpan kata sandi yang sudah di-hash.
     * Konsep Laravel: Controller (MVC), Eloquent, Hashing dan Database Transaction.
     */
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $user = new User(collect($data)->only(['name', 'identity_number', 'email'])->all());
        $user->role = $data['role'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return $this->saved($request, 'Pengguna berhasil ditambahkan.', 'users');
    }

    /**
     * Memperbarui akun dengan lock sambil melindungi role akun aktif dan administrator terakhir.
     * Konsep Laravel: Controller (MVC), Eloquent, Hashing dan Database Transaction.
     */
    public function update(UserRequest $request, int $user)
    {
        DB::transaction(function () use ($request, $user): void {
            $admins = DB::table('users')->where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $record = User::where('id', $user)->lockForUpdate()->firstOrFail();
            $data = $request->validated();
            if ($record->role !== $data['role']) {
                if ($record->id === $request->user()->id) {
                    throw ValidationException::withMessages(['role' => 'Role akun yang sedang Anda gunakan tidak dapat diubah di sini.']);
                }
                if ($record->role === 'admin' && $admins->count() <= 1) {
                    throw ValidationException::withMessages(['role' => 'Administrator terakhir tidak dapat diubah menjadi pengguna biasa.']);
                }
            }
            $record->fill(collect($data)->only(['name', 'identity_number', 'email'])->all());
            $record->role = $data['role'];
            if ($record->isDirty('email')) {
                $record->email_verified_at = null;
            }
            if (! empty($data['password'])) {
                $record->password = Hash::make($data['password']);
                $record->remember_token = null;
            }
            $record->save();
        }, 3);

        return $this->saved($request, 'Data pengguna berhasil diperbarui.', 'users');
    }

    /**
     * Menolak penghapusan akun aktif, admin terakhir, atau akun bersejarah; membersihkan avatar setelah transaksi berhasil.
     * Konsep Laravel: Controller (MVC), Eloquent, Hashing dan Database Transaction.
     */
    public function destroy(Request $request, int $user)
    {
        $avatar = DB::transaction(function () use ($request, $user): ?string {
            $admins = DB::table('users')->where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $record = User::where('id', $user)->lockForUpdate()->firstOrFail();
            if ($record->id === $request->user()->id) {
                throw ValidationException::withMessages(['user_id' => 'Akun yang sedang digunakan tidak dapat dihapus dari master data.']);
            }
            if ($record->role === 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages(['user_id' => 'Administrator terakhir tidak dapat dihapus.']);
            }
            if (DB::table('bookings')->where('user_id', $user)->orWhere('reviewed_by', $user)->exists()) {
                throw ValidationException::withMessages(['user_id' => 'Pengguna memiliki riwayat peminjaman atau pemeriksaan dan tidak dapat dihapus.']);
            }
            $avatar = $record->avatar_path;
            $record->delete();

            return $avatar;
        }, 3);

        if ($avatar) {
            DB::afterCommit(fn () => Storage::disk('public')->delete($avatar));
        }

        return $this->saved($request, 'Pengguna berhasil dihapus.', 'users');
    }
}
