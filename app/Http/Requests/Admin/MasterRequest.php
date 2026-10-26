<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class MasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view-room-master') ?? false;
    }

    public function attributes(): array
    {
        return [
            'code' => 'Kode ruangan', 'name' => 'Nama', 'capacity' => 'Kapasitas',
            'location' => 'Lokasi', 'description' => 'Deskripsi', 'photo' => 'Foto ruangan',
            'facility_ids' => 'Fasilitas', 'facility_ids.*' => 'Fasilitas',
            'identity_number' => 'NIM/NIP', 'email' => 'Email', 'role' => 'Role',
            'password' => 'Kata sandi', 'user_id' => 'Pengguna', 'room_id' => 'Ruangan',
            'purpose' => 'Keperluan', 'participant_count' => 'Jumlah peserta',
            'starts_at' => 'Waktu mulai', 'ends_at' => 'Waktu selesai',
            'rejection_reason' => 'Alasan penolakan', 'decision' => 'Keputusan',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'unique' => ':attribute sudah digunakan.',
            'exists' => ':attribute tidak ditemukan.',
            'in' => ':attribute tidak valid.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'max.file' => ':attribute maksimal :max KB.',
            'min.numeric' => ':attribute minimal :min.',
            'min.string' => ':attribute minimal :min karakter.',
            'date' => ':attribute harus berupa tanggal dan waktu yang valid.',
            'after' => 'Waktu selesai harus setelah waktu mulai.',
            'image' => 'Unggah berkas gambar yang valid.',
            'mimes' => 'Foto harus berupa JPG, PNG, atau WebP.',
            'email' => 'Masukkan alamat email yang valid.',
            'lowercase' => 'Email harus menggunakan huruf kecil.',
            'confirmed' => 'Konfirmasi kata sandi belum cocok.',
            'boolean' => ':attribute tidak valid.',
            'array' => ':attribute tidak valid.',
            'distinct' => ':attribute tidak boleh berulang.',
        ];
    }
}
