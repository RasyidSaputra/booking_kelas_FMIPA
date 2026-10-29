<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingManagement
{
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

    private function requireStatus(object $booking, string $expected): void
    {
        if ($booking->status !== $expected) {
            $this->invalid('status', 'Status pengajuan telah berubah atau aksi tidak diizinkan. Muat ulang halaman.');
        }
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
