<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rooms = [
            'FMIPA-AUD-DADANG' => 'AUDITORIUM GEDUNG PROF. DR. DADANG ISKANDAR',
            'FMIPA-101' => 'RUANG 101',
            'FMIPA-102' => 'RUANG 102',
            'FMIPA-103' => 'RUANG 103',
            'FMIPA-106' => 'RUANG 106',
            'FMIPA-107' => 'RUANG 107',
            'FMIPA-201' => 'RUANG 201',
            'FMIPA-202' => 'RUANG 202',
            'FMIPA-203' => 'RUANG 203',
            'FMIPA-205' => 'RUANG 205',
            'FMIPA-206' => 'RUANG 206',
            'FMIPA-302' => 'RUANG 302',
            'FMIPA-304' => 'RUANG 304',
            'FMIPA-308' => 'RUANG 308',
            'FMIPA-309' => 'RUANG 309',
        ];

        $normalizeName = static fn (string $name): string => mb_strtoupper(
            preg_replace('/\s+/u', ' ', trim($name))
        );

        $existingRooms = DB::table('rooms')->get(['code', 'name']);
        $existingCodes = $existingRooms->pluck('code')->all();
        $existingNames = $existingRooms->pluck('name')->map($normalizeName)->all();
        $timestamp = now();

        foreach ($rooms as $code => $name) {
            if (in_array($code, $existingCodes, true) || in_array($normalizeName($name), $existingNames, true)) {
                continue;
            }

            DB::table('rooms')->insert([
                'code' => $code,
                'name' => $name,
                // Data sumber hanya memuat nama. Lengkapi detail melalui admin
                // sebelum ruangan diaktifkan untuk peminjaman.
                'capacity' => 0,
                'location' => 'Belum ditentukan',
                'description' => 'Kapasitas dan lokasi perlu dilengkapi sebelum ruangan diaktifkan.',
                'is_active' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    public function down(): void
    {
        // Data master dipertahankan karena dapat sudah diperbarui atau memiliki
        // riwayat peminjaman. Rollback tidak menghapus data ruangan.
    }
};
