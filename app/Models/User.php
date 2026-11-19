<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * Merepresentasikan akun pengguna beserta identitas, role, avatar, dan token API.
 * Konsep Laravel: Eloquent Model, Authenticatable, Sanctum dan Attribute Casting.
 */
#[Fillable(['name', 'identity_number', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Menghasilkan URL avatar pada disk public, atau null jika pengguna belum memiliki avatar.
     * Konsep Laravel: Eloquent Model, Filesystem Storage dan URL.
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    /**
     * Mengambil huruf awal nama sebagai pengganti avatar di antarmuka.
     * Konsep Laravel: Helper pada Eloquent Model; pengolahan string PHP.
     */
    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 1));
    }

    /**
     * Mengubah nilai role tersimpan menjadi label yang mudah dibaca.
     * Konsep Laravel: Helper pada Eloquent Model; pemetaan role aplikasi.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'dosen' => 'Dosen',
            'mahasiswa' => 'Mahasiswa',
            default => 'Belum ditentukan',
        };
    }

    /**
     * Mengonversi waktu verifikasi menjadi datetime dan menerapkan hashed cast pada kata sandi.
     * Konsep Laravel: Eloquent Attribute Casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
