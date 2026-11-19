<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Memvalidasi identitas login dan membatasi percobaan autentikasi yang gagal.
 * Konsep Laravel: Form Request, Authentication dan Rate Limiting.
 */
class LoginRequest extends FormRequest
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
     * Menetapkan aturan validasi NIM/NIP dan kata sandi untuk login.
     * Konsep Laravel: Form Request Validation, rules().
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'identity_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Menyediakan pesan kesalahan validasi dalam bahasa Indonesia.
     * Konsep Laravel: Form Request, custom validation messages.
     */
    public function messages(): array
    {
        return [
            'identity_number.required' => 'NIM/NIP wajib diisi.',
            'identity_number.string' => 'NIM/NIP harus berupa teks.',
            'identity_number.max' => 'NIM/NIP maksimal 30 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
        ];
    }

    /**
     * Memeriksa batas percobaan, mencoba login dengan NIM/NIP, dan memperbarui penghitung kegagalan.
     * Konsep Laravel: Form Request, Authentication dan Rate Limiting.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('identity_number', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'identity_number' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Menolak percobaan login ketika batas tercapai serta memicu event Lockout.
     * Konsep Laravel: Form Request, Authentication dan Rate Limiting.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'identity_number' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Membentuk kunci pembatasan percobaan dari identitas pengguna dan alamat IP.
     * Konsep Laravel: Form Request, Authentication dan Rate Limiting.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('identity_number')).'|'.$this->ip());
    }
}
