<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Memvalidasi perubahan profil dan avatar tanpa menerima perubahan role dari formulir profil.
 * Konsep Laravel: Form Request, Validation, Unique Rule dan File Validation.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Mengizinkan validasi request dilanjutkan; pembatasan akses lainnya mengikuti middleware route.
     * Konsep Laravel: Form Request Authorization, authorize().
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Menetapkan aturan validasi nama, NIM/NIP, email, avatar, dan larangan perubahan role.
     * Konsep Laravel: Form Request Validation, rules().
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'identity_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
            'role' => ['prohibited'],
        ];
    }

    /**
     * Menyediakan label kolom dalam bahasa Indonesia untuk pesan kesalahan validasi.
     * Konsep Laravel: Form Request, custom validation attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama lengkap',
            'identity_number' => 'NIM / NIP',
            'email' => 'Alamat email',
            'avatar' => 'Foto profil',
        ];
    }

    /**
     * Menyediakan pesan kesalahan validasi dalam bahasa Indonesia.
     * Konsep Laravel: Form Request, custom validation messages.
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'identity_number.unique' => 'NIM / NIP ini sudah digunakan akun lain.',
            'email.unique' => 'Alamat email ini sudah digunakan akun lain.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.lowercase' => 'Gunakan huruf kecil pada alamat email.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.mimes' => 'Foto profil harus berupa JPG, PNG, atau WebP.',
            'avatar.max' => 'Ukuran foto profil maksimal 2 MB.',
            'avatar.uploaded' => 'Foto profil gagal diunggah. Coba lagi dengan file yang lebih kecil.',
            'role.prohibited' => 'Role akun tidak dapat diubah melalui pengaturan profil.',
        ];
    }
}
