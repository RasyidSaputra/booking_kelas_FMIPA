<?php

namespace App\Http\Requests\Admin;

/**
 * Memvalidasi ruangan, pengguna, peserta, serta urutan waktu jadwal admin.
 * Konsep Laravel: Form Request, Exists Rule dan Date Validation.
 */
class ScheduleRequest extends MasterRequest
{
    /**
     * Menetapkan aturan validasi pemilik, ruangan, jumlah peserta, serta urutan waktu jadwal.
     * Konsep Laravel: Form Request Validation, rules().
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'purpose' => ['required', 'string', 'max:5000'],
            'participant_count' => ['required', 'integer', 'min:1', 'max:10000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }
}
