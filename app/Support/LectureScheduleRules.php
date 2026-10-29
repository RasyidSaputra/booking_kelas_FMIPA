<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Validation\Validator;

class LectureScheduleRules
{
    public static function rules(bool $updating = false): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'semester' => ['required', 'string', 'max:100'],
            'purpose' => ['required', 'string', 'max:500'],
            'participant_count' => ['required', 'integer', 'min:1', 'max:10000'],
            'weekday' => ['required', 'integer', 'between:1,5'],
            'start_time' => ['required', 'date_format:H:i', 'after_or_equal:07:30', 'before:18:00'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', 'before_or_equal:18:00'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on', 'after_or_equal:today'],
            'effective_from' => [$updating ? 'required' : 'nullable', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:ends_on'],
        ];
    }

    public static function validatePeriod(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }
        $data = $validator->getData();
        if (CarbonImmutable::parse($data['starts_on'])->diffInDays(CarbonImmutable::parse($data['ends_on'])) > 366) {
            $validator->errors()->add('ends_on', 'Periode jadwal maksimal satu tahun (366 hari).');
        }
    }

    public static function attributes(): array
    {
        return [
            'room_id' => 'Ruangan', 'user_id' => 'Dosen / penanggung jawab',
            'purpose' => 'Mata kuliah / kelas', 'participant_count' => 'Jumlah peserta',
            'semester' => 'Semester', 'weekday' => 'Hari kuliah',
            'start_time' => 'Jam mulai', 'end_time' => 'Jam selesai',
            'starts_on' => 'Awal periode', 'ends_on' => 'Akhir periode',
            'effective_from' => 'Tanggal mulai perubahan',
        ];
    }

    public static function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'string' => ':attribute harus berupa teks.',
            'exists' => ':attribute tidak ditemukan.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'min.numeric' => ':attribute minimal :min.',
            'date_format' => 'Format :attribute harus :format.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'start_time.after_or_equal' => 'Kuliah paling awal dimulai pukul 07:30 WIB.',
            'start_time.before' => 'Kuliah harus dimulai sebelum pukul 18:00 WIB.',
            'end_time.before_or_equal' => 'Kuliah paling lambat selesai pukul 18:00 WIB.',
            'ends_on.after_or_equal' => 'Akhir periode harus pada atau setelah awal periode dan hari ini.',
            'effective_from.after_or_equal' => 'Perubahan hanya dapat diterapkan mulai hari ini atau tanggal mendatang.',
            'effective_from.before_or_equal' => 'Tanggal mulai perubahan harus berada sebelum atau pada akhir periode.',
            'weekday.between' => 'Pilih hari kuliah Senin sampai Jumat.',
        ];
    }
}
