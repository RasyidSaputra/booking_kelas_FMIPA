<?php

namespace App\Http\Requests;

use App\Http\Requests\Admin\MasterRequest;
use Illuminate\Validation\Rule;

/**
 * Memvalidasi keputusan administrator dan mewajibkan alasan saat pengajuan ditolak.
 * Konsep Laravel: Form Request, pewarisan MasterRequest dan Conditional Validation.
 */
class ReviewBookingRequest extends MasterRequest
{
    /**
     * Menetapkan aturan validasi keputusan pemeriksaan dan alasan penolakan bersyarat.
     * Konsep Laravel: Form Request Validation, rules().
     *
     * @return array<string, array>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ];
    }
}
