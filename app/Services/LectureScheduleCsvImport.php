<?php

namespace App\Services;

use App\Support\LectureScheduleRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LectureScheduleCsvImport
{
    public const HEADERS = [
        'semester', 'kode_ruangan', 'nip_dosen', 'mata_kuliah_kelas', 'hari',
        'mulai', 'selesai', 'peserta', 'awal_periode', 'akhir_periode',
    ];

    public const MAX_ROWS = 200;

    public function __construct(private LectureScheduleManagement $management) {}

    public function read(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        try {
            $first = fgets($handle);
            if ($first === false) {
                $this->invalidFile('File CSV kosong. Gunakan template yang tersedia.');
            }
            $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
            $delimiter = ',';
            if (preg_match('/^sep=([;,\t])\s*$/i', $first, $match)) {
                $delimiter = $match[1];
            } else {
                foreach ([',', ';', "\t"] as $candidate) {
                    if (count(str_getcsv($first, $candidate, '"', '')) > count(str_getcsv($first, $delimiter, '"', ''))) {
                        $delimiter = $candidate;
                    }
                }
                rewind($handle);
            }
            $header = fgetcsv($handle, 0, $delimiter, '"', '');
            if ($header === false) {
                $this->invalidFile('Header CSV tidak ditemukan. Gunakan template yang tersedia.');
            }
            $header = array_map(function (?string $value): string {
                $value = preg_replace('/^\xEF\xBB\xBF/', '', $value ?? '');

                return trim(preg_replace('/[^a-z0-9]+/', '_', mb_strtolower(trim($value))), '_');
            }, $header);
            if (count($header) !== count(self::HEADERS) || array_diff(self::HEADERS, $header) !== []) {
                $this->invalidFile('Kolom CSV harus sesuai template: '.implode(', ', self::HEADERS).'.');
            }

            $rows = [];
            $line = 1;
            while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
                $line++;
                if (count(array_filter($cells, fn ($cell) => trim($cell ?? '') !== '')) === 0) {
                    continue;
                }
                if (count($rows) >= self::MAX_ROWS) {
                    $this->invalidFile('Maksimal '.self::MAX_ROWS.' baris jadwal dalam satu file. Pisahkan menjadi beberapa file.');
                }
                foreach ($cells as $cell) {
                    if (! mb_check_encoding($cell ?? '', 'UTF-8') || str_contains($cell ?? '', "\0")) {
                        $this->invalidFile('Gunakan format CSV UTF-8. Di Excel, pilih Save As → CSV UTF-8.');
                    }
                }
                $values = array_combine($header, array_slice(array_pad($cells, count($header), ''), 0, count($header)));
                $rows[] = [
                    'line' => $line,
                    'values' => array_map(fn ($cell) => trim($cell ?? ''), $values),
                    'parse_error' => count($cells) !== count($header) ? 'Jumlah kolom berbeda dari header. Periksa pemisah dan tanda kutip.' : null,
                ];
            }
            if ($rows === []) {
                $this->invalidFile('CSV harus memuat minimal satu baris jadwal selain header.');
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    public function inspect(array $sources, bool $locking = false): array
    {
        // Konfirmasi mengunci ruangan lebih dahulu, mengikuti urutan lock proses booking.
        $rooms = DB::table('rooms')->orderBy('id')->when($locking, fn ($query) => $query->lockForUpdate())
            ->get()->keyBy(fn ($room) => $this->normalize($room->code));
        $users = DB::table('users')->whereIn('role', ['dosen', 'admin'])->whereNotNull('identity_number')
            ->get(['id', 'identity_number'])->keyBy(fn ($user) => $this->normalize($user->identity_number));
        $rows = [];
        foreach ($sources as $source) {
            $values = $source['values'];
            $room = $rooms->get($this->normalize($values['kode_ruangan']));
            $user = $users->get($this->normalize($values['nip_dosen']));
            $day = $this->normalize(str_replace(["'", '’'], '', $values['hari']));
            $data = [
                'room_id' => $room?->id, 'user_id' => $user?->id,
                'semester' => $values['semester'], 'purpose' => $values['mata_kuliah_kelas'],
                'weekday' => ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5][$day] ?? $day,
                'start_time' => $this->time($values['mulai']), 'end_time' => $this->time($values['selesai']),
                'participant_count' => $values['peserta'], 'starts_on' => $values['awal_periode'], 'ends_on' => $values['akhir_periode'],
            ];
            $validator = Validator::make($data, LectureScheduleRules::rules(), LectureScheduleRules::messages(), LectureScheduleRules::attributes());
            $validator->after([LectureScheduleRules::class, 'validatePeriod']);
            $errors = collect($validator->errors()->messages())->except(['room_id', 'user_id'])->flatten()->all();
            if (! $room) {
                $errors[] = 'Kode ruangan tidak ditemukan: '.($values['kode_ruangan'] ?: '(kosong)').'.';
            }
            if (! $user) {
                $errors[] = 'NIP dosen / administrator tidak ditemukan: '.($values['nip_dosen'] ?: '(kosong)').'.';
            }
            if ($source['parse_error']) {
                $errors[] = $source['parse_error'];
            }
            $occurrences = $errors === [] ? $this->management->occurrences($data) : [];
            if ($errors === [] && $occurrences === []) {
                $errors[] = 'Tidak ada pertemuan mendatang pada hari kuliah dalam periode tersebut.';
            }
            $rows[] = $source + [
                'data' => $data, 'status' => $errors === [] ? 'ready' : 'invalid',
                'errors' => $errors, 'occurrences' => $occurrences, 'meetings' => count($occurrences),
                'room_active' => $room?->is_active, 'capacity' => $room?->capacity,
            ];
        }

        $roomIds = collect($rows)->pluck('data.room_id')->filter()->unique();
        $existing = DB::table('lecture_schedules')->whereIn('room_id', $roomIds)
            ->when($locking, fn ($query) => $query->lockForUpdate())->get();
        $fingerprints = [];
        foreach ($existing as $schedule) {
            $fingerprints[$this->fingerprint((array) $schedule)] = 'Pola jadwal ini sudah ada. Baris dilewati tanpa mengubah jadwal yang tersimpan.';
        }
        $intervals = $this->bookedIntervals($rows, $locking);
        foreach ($rows as &$row) {
            if ($row['status'] === 'invalid') {
                continue;
            }
            $key = $this->fingerprint($row['data']);
            if (isset($fingerprints[$key])) {
                $row['status'] = 'duplicate';
                $row['errors'] = [$fingerprints[$key]];

                continue;
            }
            $fingerprints[$key] = 'Sama dengan baris '.$row['line'].'. Baris duplikat dilewati.';
            if (! $row['room_active']) {
                $row['errors'][] = 'Ruangan nonaktif. Aktifkan dan lengkapi detail ruangan melalui master data.';
            }
            if ((int) $row['data']['participant_count'] > $row['capacity']) {
                $row['errors'][] = 'Jumlah peserta melebihi kapasitas ruangan ('.$row['capacity'].' orang).';
            }
            foreach ($row['occurrences'] as $occurrence) {
                $date = substr($occurrence['starts_at'], 0, 10);
                foreach ($intervals[$row['data']['room_id']][$date] ?? [] as $interval) {
                    if ($occurrence['starts_at'] < $interval['ends_at'] && $occurrence['ends_at'] > $interval['starts_at']) {
                        $row['errors'][] = 'Bentrok pada '.CarbonImmutable::parse($occurrence['starts_at'])->format('d-m-Y H:i').' dengan '.$interval['label'].'.';
                        break 2;
                    }
                }
            }
            if ($row['errors'] !== []) {
                $row['status'] = 'invalid';

                continue;
            }
            foreach ($row['occurrences'] as $occurrence) {
                $intervals[$row['data']['room_id']][substr($occurrence['starts_at'], 0, 10)][] = $occurrence + ['label' => 'jadwal pada baris '.$row['line']];
            }
        }
        unset($row);

        return [
            'rows' => $rows,
            'ready' => count(array_filter($rows, fn ($row) => $row['status'] === 'ready')),
            'duplicate' => count(array_filter($rows, fn ($row) => $row['status'] === 'duplicate')),
            'invalid' => count(array_filter($rows, fn ($row) => $row['status'] === 'invalid')),
        ];
    }

    public function commit(array $sources, int $actor): array
    {
        return DB::transaction(function () use ($sources, $actor): array {
            $preview = $this->inspect($sources, locking: true);
            if ($preview['invalid'] > 0) {
                $messages = [];
                foreach ($preview['rows'] as $row) {
                    if ($row['status'] === 'invalid') {
                        $messages[] = 'Baris '.$row['line'].': '.implode(' ', $row['errors']);
                    }
                }
                throw ValidationException::withMessages(['csv_file' => array_merge(
                    ['Impor belum disimpan. Perbaiki semua baris bermasalah dan unggah ulang file.'],
                    array_slice($messages, 0, 20)
                )]);
            }
            $meetings = 0;
            foreach ($preview['rows'] as $row) {
                if ($row['status'] === 'ready') {
                    $this->management->save($row['data'], $actor);
                    $meetings += $row['meetings'];
                }
            }

            return ['created' => $preview['ready'], 'duplicate' => $preview['duplicate'], 'meetings' => $meetings];
        }, 3);
    }

    private function bookedIntervals(array $rows, bool $locking): array
    {
        $occurrences = collect($rows)->flatMap(fn ($row) => $row['occurrences']);
        if ($occurrences->isEmpty()) {
            return [];
        }
        $from = CarbonImmutable::parse($occurrences->min('starts_at'))->startOfDay();
        $until = CarbonImmutable::parse($occurrences->max('ends_at'))->endOfDay();
        $bookings = DB::table('bookings')->where('status', 'approved')
            ->whereIn('room_id', collect($rows)->pluck('data.room_id')->filter()->unique())
            ->where('starts_at', '<', $until)->where('ends_at', '>', $from)
            ->when($locking, fn ($query) => $query->lockForUpdate())->get(['room_id', 'starts_at', 'ends_at', 'purpose']);
        $intervals = [];
        foreach ($bookings as $booking) {
            $lastDay = CarbonImmutable::parse($booking->ends_at)->min($until)->startOfDay();
            for ($date = CarbonImmutable::parse($booking->starts_at)->max($from)->startOfDay(); $date->lte($lastDay); $date = $date->addDay()) {
                $intervals[$booking->room_id][$date->toDateString()][] = [
                    'starts_at' => $booking->starts_at, 'ends_at' => $booking->ends_at,
                    'label' => 'jadwal disetujui: '.$booking->purpose,
                ];
            }
        }

        return $intervals;
    }

    private function fingerprint(array $data): string
    {
        return hash('sha256', json_encode([
            (int) $data['room_id'], (int) $data['user_id'], $this->normalize($data['semester']), $this->normalize($data['purpose']),
            (int) $data['weekday'], substr($data['start_time'], 0, 5), substr($data['end_time'], 0, 5),
            (int) $data['participant_count'], $data['starts_on'], $data['ends_on'],
        ]));
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($value)));
    }

    private function time(string $value): string
    {
        return preg_match('/^(\d{1,2}):(\d{2})(?::00)?$/', $value, $matches)
            ? sprintf('%02d:%s', (int) $matches[1], $matches[2]) : $value;
    }

    private function invalidFile(string $message): never
    {
        throw ValidationException::withMessages(['csv_file' => $message]);
    }
}
