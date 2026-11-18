<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
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

    public function attributes(): array
    {
        return [
            'name' => 'Nama lengkap',
            'identity_number' => 'NIM / NIP',
            'email' => 'Alamat email',
            'avatar' => 'Foto profil',
        ];
    }

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
