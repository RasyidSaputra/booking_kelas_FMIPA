<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

/**
 * Memvalidasi nama fasilitas agar memenuhi batas panjang dan tidak duplikat.
 * Konsep Laravel: Form Request dan Unique Rule.
 */
class FacilityRequest extends MasterRequest
{
    /**
     * Menetapkan aturan validasi nama fasilitas unik.
     * Konsep Laravel: Form Request Validation, rules().
     */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100', Rule::unique('facilities')->ignore($this->route('facility'))]];
    }
}
