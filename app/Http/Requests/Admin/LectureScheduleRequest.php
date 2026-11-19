<?php

namespace App\Http\Requests\Admin;

use App\Support\LectureScheduleRules;

/**
 * Memvalidasi pola kuliah mingguan menggunakan aturan bersama milik aplikasi.
 * Konsep Laravel: Form Request, Validation dan After Validation Hook.
 */
class LectureScheduleRequest extends MasterRequest
{
    /**
     * Menggunakan aturan jadwal bersama dengan penyesuaian untuk request perubahan PATCH.
     * Konsep Laravel: Form Request Validation dan class pendukung aplikasi.
     */
    public function rules(): array
    {
        return LectureScheduleRules::rules($this->isMethod('PATCH'));
    }

    /**
     * Mendaftarkan pemeriksaan rentang periode setelah aturan validasi utama dijalankan.
     * Konsep Laravel: Form Request, After Validation Hook.
     */
    public function after(): array
    {
        return [LectureScheduleRules::validatePeriod(...)];
    }

    /**
     * Menyediakan label kolom dalam bahasa Indonesia untuk pesan kesalahan validasi.
     * Konsep Laravel: Form Request, custom validation attributes.
     */
    public function attributes(): array
    {
        return LectureScheduleRules::attributes() + parent::attributes();
    }

    /**
     * Menyediakan pesan kesalahan validasi dalam bahasa Indonesia.
     * Konsep Laravel: Form Request, custom validation messages.
     */
    public function messages(): array
    {
        return LectureScheduleRules::messages() + parent::messages();
    }
}
