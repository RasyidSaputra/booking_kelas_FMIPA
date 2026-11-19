<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Memvalidasi akun, role, identitas unik, serta kata sandi dari formulir admin.
 * Konsep Laravel: Form Request, Unique Rule dan Password Validation.
 */
class UserRequest extends MasterRequest
{
    /**
     * Menetapkan aturan validasi identitas akun unik, role, dan konfirmasi kata sandi.
     * Konsep Laravel: Form Request Validation, rules().
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'identity_number' => ['required', 'string', 'max:30', Rule::unique('users')->ignore($user)],
            'email' => ['required', 'string', 'email', 'lowercase', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'dosen', 'mahasiswa'])],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::defaults(), 'confirmed'],
        ];
    }
}
