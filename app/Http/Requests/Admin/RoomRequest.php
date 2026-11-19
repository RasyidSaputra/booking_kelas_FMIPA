<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

/**
 * Memvalidasi data ruangan, foto, kapasitas, dan daftar fasilitas dari administrator.
 * Konsep Laravel: Form Request, File Validation, Array Validation dan Unique Rule.
 */
class RoomRequest extends MasterRequest
{
    /**
     * Menetapkan aturan validasi identitas ruangan, kapasitas, foto, dan daftar fasilitas.
     * Konsep Laravel: Form Request Validation, rules().
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('rooms')->ignore($this->route('room'))],
            'name' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10000'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'distinct', 'exists:facilities,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
