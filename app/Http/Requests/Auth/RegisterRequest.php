<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
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

    public function attributes(): array
    {
        return [
            'name' => 'Nama lengkap',
            'identity_number' => 'NIM / NIP',
            'email' => 'Alamat email',
            'password' => 'Kata sandi',
        ];
    }

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
