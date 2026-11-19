<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Memvalidasi data pengajuan, tanggal, jam operasional, dan jumlah peserta.
 * Konsep Laravel: Form Request, Authorization dan Validation.
 */
class StoreBookingRequest extends FormRequest
{
    /**
     * Mengizinkan pengajuan hanya jika request memiliki pengguna terautentikasi.
     * Konsep Laravel: Form Request Authorization, authorize().
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Menetapkan aturan validasi ruangan, tanggal, jam operasional, tujuan, dan jumlah peserta.
     * Konsep Laravel: Form Request Validation, rules().
     */
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i', 'after_or_equal:07:30', 'before:18:00'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', 'before_or_equal:18:00'],
            'purpose' => ['required', 'string', 'max:500'],
            'participant_count' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }
}
