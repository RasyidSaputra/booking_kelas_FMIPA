<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LectureScheduleManagement
{
    public function __construct(private BookingManagement $bookings) {}

    public function save(array $data, int $actor, ?int $id = null): int
    {
        return DB::transaction(function () use ($data, $actor, $id): int {
            $snapshot = $id ? DB::table('lecture_schedules')->find($id) : null;
            abort_if($id && ! $snapshot, 404);
            DB::table('rooms')->whereIn('id', array_unique(array_filter([$data['room_id'], $snapshot?->room_id])))
                ->orderBy('id')->lockForUpdate()->get();

            $from = CarbonImmutable::parse($data['effective_from'] ?? today()->toDateString())->startOfDay();
            $exceptions = [];
            if ($id) {
                $series = DB::table('lecture_schedules')->where('id', $id)->lockForUpdate()->first();
                if ($series->room_id !== $snapshot->room_id) {
                    throw ValidationException::withMessages(['room_id' => 'Jadwal telah berubah. Muat ulang halaman dan coba kembali.']);
                }
                // Pengecualian satu pertemuan tetap berlaku saat pola mingguan diubah.
                $exceptions = DB::table('bookings')->where('lecture_schedule_id', $id)
                    ->where('is_lecture_exception', true)->lockForUpdate()->pluck('lecture_date')->all();
                $exceptions = array_merge($exceptions, DB::table('bookings')->where('lecture_schedule_id', $id)
                    ->where('status', 'approved')->where('starts_at', '<=', now())->lockForUpdate()->pluck('lecture_date')->all());
                DB::table('bookings')->where('lecture_schedule_id', $id)->where('status', 'approved')
                    ->where('is_lecture_exception', false)->where('starts_at', '>=', $from->toDateTimeString())
                    ->where('starts_at', '>', now())->update([
                        'status' => 'cancelled', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'updated_at' => now(),
                    ]);
            }

            $values = array_intersect_key($data, array_flip([
                'room_id', 'user_id', 'semester', 'purpose', 'participant_count',
                'weekday', 'start_time', 'end_time', 'starts_on', 'ends_on',
            ])) + ['cancelled_from' => null, 'updated_at' => now()];
            if ($id) {
                DB::table('lecture_schedules')->where('id', $id)->update($values);
            } else {
                $id = DB::table('lecture_schedules')->insertGetId($values + ['created_at' => now()]);
            }

            $count = 0;
            foreach ($this->occurrences($data, $from) as $occurrence) {
                $count++;
                if (in_array(substr($occurrence['starts_at'], 0, 10), $exceptions, true)) {
                    continue;
                }
                $this->bookings->saveSchedule([
                    'room_id' => $data['room_id'], 'user_id' => $data['user_id'],
                    'purpose' => $data['purpose'], 'participant_count' => $data['participant_count'],
                ] + $occurrence, $actor, lectureScheduleId: $id);
            }
            if ($count === 0) {
                throw ValidationException::withMessages(['ends_on' => 'Tidak ada pertemuan mendatang pada hari kuliah dalam periode tersebut.']);
            }

            return $id;
        }, 3);
    }

    public function occurrences(array $data, ?CarbonImmutable $from = null): array
    {
        $occurrences = [];
        $start = CarbonImmutable::parse($data['starts_on'])->max($from ?? CarbonImmutable::today());
        $end = CarbonImmutable::parse($data['ends_on']);
        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            $startsAt = $date->setTimeFromTimeString($data['start_time']);
            if ($date->dayOfWeekIso === (int) $data['weekday'] && $startsAt->gt(now())) {
                $occurrences[] = [
                    'starts_at' => $startsAt->toDateTimeString(),
                    'ends_at' => $date->setTimeFromTimeString($data['end_time'])->toDateTimeString(),
                ];
            }
        }

        return $occurrences;
    }

    public function cancel(int $id, string $from, int $actor): void
    {
        DB::transaction(function () use ($id, $from, $actor): void {
            $snapshot = DB::table('lecture_schedules')->find($id);
            abort_if(! $snapshot, 404);
            // Pertemuan yang dipindah ke ruangan lain juga perlu dikunci.
            $roomIds = DB::table('bookings')->where('lecture_schedule_id', $id)->pluck('room_id')->push($snapshot->room_id);
            DB::table('rooms')->whereIn('id', $roomIds)->orderBy('id')->lockForUpdate()->get();
            $series = DB::table('lecture_schedules')->where('id', $id)->lockForUpdate()->first();
            if ($series->room_id !== $snapshot->room_id) {
                throw ValidationException::withMessages(['effective_from' => 'Jadwal telah berubah. Muat ulang halaman dan coba kembali.']);
            }
            DB::table('bookings')->where('lecture_schedule_id', $id)->where('status', 'approved')
                ->where('starts_at', '>=', $from.' 00:00:00')->where('starts_at', '>', now())
                ->update(['status' => 'cancelled', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'updated_at' => now()]);
            DB::table('lecture_schedules')->where('id', $id)->update([
                'cancelled_from' => $series->cancelled_from ? min($series->cancelled_from, $from) : $from,
                'updated_at' => now(),
            ]);
        }, 3);
    }
}
