<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

/**
 * Memvalidasi data pendaftaran akun sebelum controller menyimpan pengguna baru.
 * Konsep Laravel: Form Request, Validation dan Unique Rule.
 */
class RegisterRequest extends FormRequest
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
     * Menetapkan aturan validasi identitas akun unik dan konfirmasi kata sandi saat registrasi.
     * Konsep Laravel: Form Request Validation, rules().
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'identity_number' => ['required', 'string', 'max:30', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
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
            'password' => 'Kata sandi',
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
            'identity_number.unique' => 'NIM / NIP ini sudah terdaftar.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.lowercase' => 'Gunakan huruf kecil pada alamat email.',
            'password.confirmed' => 'Konfirmasi kata sandi belum cocok.',
            'password.min' => 'Kata sandi minimal :min karakter.',
        ];
    }
}
