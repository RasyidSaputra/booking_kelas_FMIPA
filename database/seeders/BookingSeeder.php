<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data booking lama agar tidak bentrok
        DB::table('bookings')->delete();

        $admin = User::where('role', 'admin')->first() ?? User::first();
        $student = User::where('email', 'ghalib@example.com')->first() ?? User::where('role', 'mahasiswa')->first() ?? $admin;

        // Ambil dosen-dosen pengampu
        $dosenList = User::where('role', 'dosen')->get()->keyBy('name');

        $getDosenId = function (string $name) use ($dosenList, $admin): int {
            return $dosenList->get($name)?->id ?? $admin->id;
        };

        // Ruangan aktif
        $rooms = DB::table('rooms')->where('is_active', true)->get()->keyBy('code');

        if ($rooms->isEmpty()) {
            return;
        }

        // Template jadwal perkuliahan wajib mingguan (Senin - Jumat)
        // 1 = Senin, 2 = Selasa, 3 = Rabu, 4 = Kamis, 5 = Jumat
        // Ruangan 107, 108, 109, 206, 207, 208, 209, 306, 307, 308, 309 sengaja DISISAKAN KOSONG untuk bebas dibooking
        $weeklySchedule = [
            // SENIN (1)
            1 => [
                ['room' => 'FMIPA-101', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kalkulus I (Kelas A)', 'dosen' => 'Dr. Ardiansyah, M.Si.'],
                ['room' => 'FMIPA-101', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Fisika Dasar I (Kelas A)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-102', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Algoritma & Pemrograman (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-103', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kimia Dasar (Kelas A)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-104', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Biologi Umum (Kelas A)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-105', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Aljabar Linier Elementer (Kelas B)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-106', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Struktur Data (Kelas B)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-201', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Persamaan Diferensial Biasa (Kelas A)', 'dosen' => 'Dr. Ardiansyah, M.Si.'],
                ['room' => 'FMIPA-202', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Sistem Basis Data (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-203', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Termodinamika & Gelombang (Kelas A)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-204', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kimia Organik I (Kelas B)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-205', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Ekologi & Biosistematika (Kelas B)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-301', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Metode Numerik (Kelas B)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-302', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Rekayasa Perangkat Lunak (Kelas B)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-303', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Kimia Anorganik (Kelas A)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-304', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Mekanika Klasik (Kelas B)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
            ],
            // SELASA (2)
            2 => [
                ['room' => 'FMIPA-101', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Fisika Dasar II (Kelas B)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-102', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kalkulus II (Kelas B)', 'dosen' => 'Dr. Ardiansyah, M.Si.'],
                ['room' => 'FMIPA-103', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Genetika Molekuler (Kelas A)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-104', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Statistika Matematika (Kelas A)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-105', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Sistem Operasi (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-201', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Biokimia Dasar (Kelas B)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-202', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Aljabar Linier Elementer (Kelas B)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-203', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Algoritma & Pemrograman (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-205', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Fisika Matematika (Kelas A)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-301', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Rekayasa Perangkat Lunak (Kelas B)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-305', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kimia Fisik (Kelas A)', 'dosen' => 'Dewi Sartika, M.Si.'],
            ],
            // RABU (3)
            3 => [
                ['room' => 'FMIPA-101', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kalkulus I (Kelas A)', 'dosen' => 'Dr. Ardiansyah, M.Si.'],
                ['room' => 'FMIPA-101', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Struktur Data (Kelas B)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-102', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Fisika Dasar I (Kelas A)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-102', 'start' => '15:30', 'end' => '17:10', 'purpose' => 'Perkuliahan Wajib: Metode Numerik (Kelas B)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-103', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kimia Organik I (Kelas B)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-104', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Ekologi & Biosistematika (Kelas B)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-105', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Statistika Matematika (Kelas A)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-106', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Sistem Basis Data (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-201', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Termodinamika & Gelombang (Kelas A)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-202', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Aljabar Linier Elementer (Kelas B)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-203', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Genetika Molekuler (Kelas A)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-204', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Kimia Anorganik (Kelas A)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-205', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Sistem Operasi (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-301', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Persamaan Diferensial Biasa (Kelas A)', 'dosen' => 'Dr. Ardiansyah, M.Si.'],
                ['room' => 'FMIPA-302', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Mekanika Klasik (Kelas B)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-303', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Biologi Umum (Kelas A)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-304', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Rekayasa Perangkat Lunak (Kelas B)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
            ],
            // KAMIS (4)
            4 => [
                ['room' => 'FMIPA-101', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Fisika Dasar II (Kelas B)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-102', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Algoritma & Pemrograman (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-103', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Biokimia Dasar (Kelas B)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-104', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kalkulus II (Kelas B)', 'dosen' => 'Dr. Ardiansyah, M.Si.'],
                ['room' => 'FMIPA-201', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kimia Dasar (Kelas A)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-202', 'start' => '13:00', 'end' => '14:40', 'purpose' => 'Perkuliahan Wajib: Sistem Operasi (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-203', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Metode Numerik (Kelas B)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-301', 'start' => '10:00', 'end' => '11:40', 'purpose' => 'Perkuliahan Wajib: Ekologi & Biosistematika (Kelas B)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-302', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Sistem Basis Data (Kelas A)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
            ],
            // JUMAT (5)
            5 => [
                ['room' => 'FMIPA-101', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Persamaan Diferensial Biasa (Kelas A)', 'dosen' => 'Dr. Ardiansyah, M.Si.'],
                ['room' => 'FMIPA-102', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Kimia Anorganik (Kelas A)', 'dosen' => 'Dewi Sartika, M.Si.'],
                ['room' => 'FMIPA-103', 'start' => '09:45', 'end' => '11:15', 'purpose' => 'Perkuliahan Wajib: Statistika Matematika (Kelas A)', 'dosen' => 'Nurul Hidayati, S.Si., M.Mat.'],
                ['room' => 'FMIPA-201', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Struktur Data (Kelas B)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
                ['room' => 'FMIPA-202', 'start' => '13:30', 'end' => '15:10', 'purpose' => 'Perkuliahan Wajib: Fisika Dasar I (Kelas A)', 'dosen' => 'Dr. Eng. Rinaldi, M.T.'],
                ['room' => 'FMIPA-301', 'start' => '08:00', 'end' => '09:40', 'purpose' => 'Perkuliahan Wajib: Genetika Molekuler (Kelas A)', 'dosen' => 'Bambang Hendrawan, M.Sc., Ph.D.'],
                ['room' => 'FMIPA-303', 'start' => '13:30', 'end' => '15:10', 'purpose' => 'Perkuliahan Wajib: Rekayasa Perangkat Lunak (Kelas B)', 'dosen' => 'Prof. Dr. Siti Rahmah, M.Kom.'],
            ],
        ];

        // Buat jadwal perkuliahan untuk seluruh hari kerja di bulan aktif (sekitar 1 bulan)
        $today = CarbonImmutable::today();
        $startDate = $today->startOfMonth()->startOfWeek(CarbonInterface::MONDAY);
        $endDate = $today->endOfMonth()->endOfWeek(CarbonInterface::FRIDAY);

        $bookingRecords = [];

        for ($date = $startDate; $date->lte($endDate); $date = $date->addDay()) {
            $dayOfWeek = (int) $date->dayOfWeekIso; // 1 (Senin) .. 7 (Minggu)

            if (! isset($weeklySchedule[$dayOfWeek])) {
                continue; // Sabtu & Minggu kosong tanpa jadwal kuliah
            }

            $classes = $weeklySchedule[$dayOfWeek];

            foreach ($classes as $item) {
                $room = $rooms->get($item['room']);
                if (! $room) {
                    continue;
                }

                $dosenId = $getDosenId($item['dosen']);
                $startsAt = $date->setTimeFromTimeString($item['start']);
                $endsAt = $date->setTimeFromTimeString($item['end']);

                $bookingRecords[] = [
                    'user_id' => $dosenId,
                    'room_id' => $room->id,
                    'purpose' => $item['purpose'],
                    'participant_count' => min($room->capacity, 40),
                    'starts_at' => $startsAt->toDateTimeString(),
                    'ends_at' => $endsAt->toDateTimeString(),
                    'status' => 'approved',
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => $startsAt->subDays(7)->toDateTimeString(),
                    'rejection_reason' => null,
                    'created_at' => $startsAt->subDays(14)->toDateTimeString(),
                    'updated_at' => $startsAt->subDays(7)->toDateTimeString(),
                ];
            }
        }

        // Tambahkan beberapa peminjaman mahasiswa (non-matakuliah) untuk variasi
        // Misalnya di salah satu ruangan yang bebas (FMIPA-108 & FMIPA-208)
        if ($student && $rooms->has('FMIPA-108')) {
            $room108 = $rooms->get('FMIPA-108');
            // Seminar Proposal (Approved) pada Jumat minggu ini
            $fridayDate = $today->next(CarbonInterface::FRIDAY);
            $bookingRecords[] = [
                'user_id' => $student->id,
                'room_id' => $room108->id,
                'purpose' => 'Seminar Proposal Tugas Akhir: Implementasi Sistem Pemesanan Ruang Kelas Berbasis Web',
                'participant_count' => 15,
                'starts_at' => $fridayDate->setTime(14, 0)->toDateTimeString(),
                'ends_at' => $fridayDate->setTime(16, 0)->toDateTimeString(),
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->toDateTimeString(),
                'rejection_reason' => null,
                'created_at' => now()->subDay()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        if ($student && $rooms->has('FMIPA-208')) {
            $room208 = $rooms->get('FMIPA-208');
            // Rapat HIMA / BEM (Pending) pada besok lusa
            $nextDate = $today->addDays(2);
            $bookingRecords[] = [
                'user_id' => $student->id,
                'room_id' => $room208->id,
                'purpose' => 'Rapat Koordinasi Panitia MIPA EXPO 2026',
                'participant_count' => 25,
                'starts_at' => $nextDate->setTime(15, 30)->toDateTimeString(),
                'ends_at' => $nextDate->setTime(17, 30)->toDateTimeString(),
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        // Simpan dalam batch untuk efisiensi
        foreach (array_chunk($bookingRecords, 100) as $chunk) {
            DB::table('bookings')->insert($chunk);
        }
    }
}
