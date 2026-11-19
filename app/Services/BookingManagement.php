<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Memusatkan aturan booking agar alur web dan API memakai pemeriksaan yang sama.
 * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
 */
class BookingManagement
{
    /**
     * Menyimpan pengajuan pending setelah memeriksa waktu, kapasitas, dan bentrok dalam transaksi.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    public function submit(array $data, int $actor): int
    {
        return DB::transaction(function () use ($data, $actor): int {
            $room = DB::table('rooms')->where('id', $data['room_id'])->lockForUpdate()->first();
            $startsAt = CarbonImmutable::parse($data['booking_date'].' '.$data['start_time']);
            if ($startsAt->lte(now())) {
                $this->invalid('start_time', 'Waktu mulai peminjaman sudah lewat.');
            }
            $values = [
                'room_id' => (int) $data['room_id'], 'user_id' => $actor,
                'purpose' => $data['purpose'],
                'participant_count' => $data['participant_count'] ?? $room?->capacity ?? 0,
                'starts_at' => $startsAt->toDateTimeString(),
                'ends_at' => CarbonImmutable::parse($data['booking_date'].' '.$data['end_time'])->toDateTimeString(),
            ];
            $this->checkAvailability($room, $values);

            return DB::table('bookings')->insertGetId($values + [
                'status' => 'pending', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }, 3);
    }

    /**
     * Merevisi booking milik peminjam; booking yang sudah disetujui diajukan ulang untuk persetujuan.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    public function reviseForOwner(int $id, array $data, int $actor): bool
    {
        return DB::transaction(function () use ($id, $data, $actor): bool {
            [$booking, $room] = $this->lockForOwner($id, $actor);
            $startsAt = CarbonImmutable::parse($data['booking_date'].' '.$data['start_time']);
            if ($startsAt->lte(now())) {
                $this->invalid('start_time', 'Waktu mulai peminjaman sudah lewat.');
            }
            $values = [
                'room_id' => $booking->room_id,
                'purpose' => $data['purpose'],
                'participant_count' => $data['participant_count'] ?? $booking->participant_count,
                'starts_at' => $startsAt->toDateTimeString(),
                'ends_at' => CarbonImmutable::parse($data['booking_date'].' '.$data['end_time'])->toDateTimeString(),
            ];
            $this->checkAvailability($room, $values, $id);
            $requiresApproval = $booking->status === 'approved';
            if ($requiresApproval) {
                $values += ['status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null, 'rejection_reason' => null];
            }
            if ($booking->lecture_schedule_id !== null) {
                $values['is_lecture_exception'] = true;
            }
            DB::table('bookings')->where('id', $id)->update($values + ['updated_at' => now()]);

            return $requiresApproval;
        }, 3);
    }

    /**
     * Membatalkan booking milik peminjam tanpa menghapus riwayat atau pengecualian pertemuan.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    public function cancelForOwner(int $id, int $actor): void
    {
        DB::transaction(function () use ($id, $actor): void {
            [$booking] = $this->lockForOwner($id, $actor);
            DB::table('bookings')->where('id', $id)->update([
                'status' => 'cancelled', 'updated_at' => now(),
                'is_lecture_exception' => $booking->lecture_schedule_id !== null,
            ]);
        }, 3);
    }

    /**
     * Mengunci ruangan dan booking, lalu memeriksa kepemilikan, status, dan batas waktu perubahan.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    private function lockForOwner(int $id, int $actor): array
    {
        $snapshot = DB::table('bookings')->find($id);
        abort_if(! $snapshot, 404, 'Booking tidak ditemukan.');
        abort_if((int) $snapshot->user_id !== $actor, 403, 'Anda tidak memiliki hak akses untuk mengubah booking ini.');

        // Urutan penguncian sama dengan persetujuan admin: ruangan, lalu booking.
        $room = DB::table('rooms')->where('id', $snapshot->room_id)->lockForUpdate()->first();
        $booking = DB::table('bookings')->where('id', $id)->lockForUpdate()->first();
        abort_if(! $booking, 404, 'Booking tidak ditemukan.');
        abort_if((int) $booking->user_id !== $actor, 403, 'Anda tidak memiliki hak akses untuk mengubah booking ini.');
        if ($booking->room_id !== $snapshot->room_id) {
            $this->invalid('room_id', 'Jadwal telah berubah. Muat ulang halaman dan coba kembali.');
        }
        if (! in_array($booking->status, ['pending', 'approved'], true)) {
            $this->invalid('status', 'Booking dengan status ini tidak dapat diubah.');
        }
        if (CarbonImmutable::parse($booking->ends_at)->lte(now())) {
            $this->invalid('status', 'Booking yang sudah selesai tidak dapat diubah.');
        }
        if ($booking->lecture_schedule_id !== null && CarbonImmutable::parse($booking->starts_at)->lte(now())) {
            $this->invalid('status', 'Pertemuan kuliah yang sudah dimulai tetap disimpan sebagai riwayat.');
        }

        return [$booking, $room];
    }

    /**
     * Membuat atau mengubah jadwal disetujui, termasuk pertemuan kuliah, setelah memeriksa ketersediaan.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    public function saveSchedule(array $data, int $actor, ?int $id = null, ?int $lectureScheduleId = null): void
    {
        DB::transaction(function () use ($data, $actor, $id, $lectureScheduleId): void {
            $snapshot = $id ? DB::table('bookings')->find($id) : null;
            abort_if($id && ! $snapshot, 404);
            $rooms = DB::table('rooms')->whereIn('id', array_unique(array_filter([$data['room_id'], $snapshot?->room_id])))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if (! DB::table('users')->where('id', $data['user_id'])->lockForUpdate()->first(['id'])) {
                $this->invalid('user_id', 'Pengguna sudah tidak tersedia. Muat ulang halaman.');
            }
            if ($id) {
                $booking = DB::table('bookings')->where('id', $id)->lockForUpdate()->first();
                abort_if(! $booking, 404);
                $this->requireStatus($booking, 'approved');
                if ($booking->room_id !== $snapshot->room_id) {
                    $this->invalid('room_id', 'Jadwal telah berubah. Muat ulang halaman dan coba kembali.');
                }
            }

            $values = [
                'user_id' => (int) $data['user_id'], 'room_id' => (int) $data['room_id'],
                'purpose' => $data['purpose'], 'participant_count' => (int) $data['participant_count'],
                'starts_at' => CarbonImmutable::parse($data['starts_at'])->setTimezone(config('app.timezone'))->toDateTimeString(),
                'ends_at' => CarbonImmutable::parse($data['ends_at'])->setTimezone(config('app.timezone'))->toDateTimeString(),
            ];
            if ($id && $booking->lecture_schedule_id !== null
                && (CarbonImmutable::parse($booking->starts_at)->lte(now()) || CarbonImmutable::parse($values['starts_at'])->lte(now()))) {
                $this->invalid('starts_at', 'Hanya pertemuan kuliah yang belum dimulai yang dapat diubah, dengan waktu pengganti di masa mendatang.');
            }
            $this->checkAvailability($rooms->get($data['room_id']), $values, $id);
            if ($lectureScheduleId !== null) {
                $values += ['lecture_schedule_id' => $lectureScheduleId, 'lecture_date' => substr($values['starts_at'], 0, 10)];
            } elseif ($id && $booking->lecture_schedule_id !== null) {
                $values['is_lecture_exception'] = true;
            }
            $values += ['status' => 'approved', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'rejection_reason' => null, 'updated_at' => now()];
            if ($id) {
                DB::table('bookings')->where('id', $id)->update($values);
            } else {
                DB::table('bookings')->insert($values + ['created_at' => now()]);
            }
        }, 3);
    }

    /**
     * Menyetujui atau menolak pengajuan pending dan mencatat pemeriksa serta alasan penolakan.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    public function review(int $id, string $decision, ?string $reason, int $actor): void
    {
        DB::transaction(function () use ($id, $decision, $reason, $actor): void {
            $snapshot = DB::table('bookings')->find($id);
            abort_if(! $snapshot, 404);
            $room = DB::table('rooms')->where('id', $snapshot->room_id)->lockForUpdate()->first();
            $booking = DB::table('bookings')->where('id', $id)->lockForUpdate()->first();
            abort_if(! $booking, 404);
            $this->requireStatus($booking, 'pending');
            if ($booking->room_id !== $snapshot->room_id) {
                $this->invalid('room_id', 'Pengajuan telah berubah. Muat ulang halaman.');
            }
            if ($decision === 'approved') {
                $this->checkAvailability($room, (array) $booking);
            }
            DB::table('bookings')->where('id', $id)->update([
                'status' => $decision, 'reviewed_by' => $actor, 'reviewed_at' => now(),
                'rejection_reason' => $decision === 'rejected' ? $reason : null, 'updated_at' => now(),
            ]);
        }, 3);
    }

    /**
     * Membatalkan jadwal melalui admin dan mencatat pemeriksa tanpa menghapus riwayat.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    public function cancel(int $id, int $actor): void
    {
        DB::transaction(function () use ($id, $actor): void {
            $snapshot = DB::table('bookings')->find($id);
            abort_if(! $snapshot, 404);
            DB::table('rooms')->where('id', $snapshot->room_id)->lockForUpdate()->first();
            $booking = DB::table('bookings')->where('id', $id)->lockForUpdate()->first();
            abort_if(! $booking, 404);
            $this->requireStatus($booking, 'approved');
            if ($booking->room_id !== $snapshot->room_id) {
                $this->invalid('room_id', 'Jadwal telah berubah. Muat ulang halaman.');
            }
            if ($booking->lecture_schedule_id !== null && CarbonImmutable::parse($booking->starts_at)->lte(now())) {
                $this->invalid('starts_at', 'Pertemuan kuliah yang sudah dimulai tetap disimpan sebagai riwayat.');
            }
            DB::table('bookings')->where('id', $id)->update([
                'status' => 'cancelled', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'updated_at' => now(),
                'is_lecture_exception' => $booking->lecture_schedule_id !== null,
            ]);
        }, 3);
    }

    /**
     * Menolak ruangan nonaktif, peserta berlebih, jam di luar operasional, atau bentrok jadwal disetujui.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    private function checkAvailability(?object $room, array $data, ?int $ignore = null): void
    {
        if (! $room || ! $room->is_active) {
            $this->invalid('room_id', 'Ruangan nonaktif atau tidak tersedia.');
        }
        if ($data['participant_count'] < 1 || $data['participant_count'] > $room->capacity) {
            $this->invalid('participant_count', 'Jumlah peserta melebihi kapasitas ruangan atau tidak valid.');
        }
        $startsAt = CarbonImmutable::parse($data['starts_at']);
        $endsAt = CarbonImmutable::parse($data['ends_at']);
        if ($endsAt->lte($startsAt)) {
            $this->invalid('ends_at', 'Waktu selesai harus setelah waktu mulai.');
        }
        if (! $startsAt->isSameDay($endsAt)) {
            $this->invalid('ends_at', 'Jadwal peminjaman harus selesai pada hari yang sama (gedung tutup pukul 18:00 WIB).');
        }
        if ($startsAt->format('H:i') < '07:30') {
            $this->invalid('starts_at', 'Jadwal penggunaan ruangan paling awal dimulai pukul 07:30 WIB.');
        }
        if ($endsAt->format('H:i') > '18:00') {
            $this->invalid('ends_at', 'Gedung tidak dapat digunakan setelah pukul 18:00 WIB.');
        }
        // Gunakan current/locking read: pada MySQL REPEATABLE READ, snapshot
        // dari sebelum menunggu lock ruangan tidak boleh dipakai untuk bentrok.
        $conflict = DB::table('bookings')->where('room_id', $room->id)->where('status', 'approved')
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore))
            ->where('starts_at', '<', $data['ends_at'])->where('ends_at', '>', $data['starts_at'])->lockForUpdate()->first(['id']);
        if ($conflict) {
            $this->invalid('starts_at', 'Jadwal bertabrakan dengan jadwal kuliah atau peminjaman yang sudah disetujui pada '.$startsAt->format('d-m-Y H:i').'. Pilih waktu lain.');
        }
    }

    /**
     * Memastikan status booking sesuai prasyarat sebelum perubahan dilanjutkan.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    private function requireStatus(object $booking, string $expected): void
    {
        if ($booking->status !== $expected) {
            $this->invalid('status', 'Status pengajuan telah berubah atau aksi tidak diizinkan. Muat ulang halaman.');
        }
    }

    /**
     * Menghentikan proses dengan ValidationException yang dikaitkan ke kolom tertentu.
     * Konsep Laravel: Service Layer (pola aplikasi), Query Builder dan Database Transaction.
     */
    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
