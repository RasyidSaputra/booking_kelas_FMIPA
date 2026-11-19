<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Menyiapkan request perubahan ruangan; otorisasi masih false dan aturan masih kosong.
 * Konsep Laravel: Form Request; kerangka belum diimplementasikan.
 */
class UpdateRoomRequest extends FormRequest
{
    /**
     * Menolak request karena otorisasi pada kerangka ini belum diimplementasikan.
     * Konsep Laravel: Form Request Authorization, authorize().
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Mengembalikan aturan kosong; validasi pada kerangka ini belum diimplementasikan.
     * Konsep Laravel: Form Request Validation, rules().
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
