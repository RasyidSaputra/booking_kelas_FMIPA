<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class RoomCalendar
{
    /**
     * Ringkasan harian berdasarkan ruang aktif dengan booking disetujui.
     * Satu ruangan dihitung sekali per tanggal, bukan per pengajuan.
     */
    public function forMonth(CarbonImmutable $month): array
    {
        $rooms = DB::table('rooms')->where('is_active', true)->orderBy('name')->get();
        $bookings = DB::table('bookings')
            ->whereIn('room_id', $rooms->pluck('id'))
            ->where('status', 'approved')
            ->where('starts_at', '<', $month->addMonth()->toDateTimeString())
            ->where('ends_at', '>', $month->toDateTimeString())
            ->orderBy('starts_at')
            ->get(['room_id', 'starts_at', 'ends_at'])
            ->map(function (object $booking): object {
                $booking->starts_at = CarbonImmutable::parse($booking->starts_at);
                $booking->ends_at = CarbonImmutable::parse($booking->ends_at);

                return $booking;
            });

        $days = [];
        $schedule = [];
        $today = CarbonImmutable::today();
        $lastDay = $month->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);

        for ($date = $month->startOfWeek(CarbonInterface::MONDAY); $date->lte($lastDay); $date = $date->addDay()) {
            $inMonth = $date->isSameMonth($month);
            // Interval setengah terbuka: booking yang berakhir pukul 00:00
            // tidak ikut memenuhi tanggal berikutnya.
            $daily = $bookings->filter(fn (object $booking): bool => $booking->starts_at->lt($date->addDay()) && $booking->ends_at->gt($date));
            $occupied = $daily->pluck('room_id')->unique()->count();
            $percentage = $rooms->isEmpty() ? 0 : ($occupied / $rooms->count()) * 100;
            $status = match (true) {
                $rooms->isEmpty() => 'empty',
                $percentage >= 80 => 'busy',
                $percentage >= 40 => 'moderate',
                default => 'quiet',
            };

            $days[] = [
                'date' => $date,
                'in_month' => $inMonth,
                'today' => $date->isSameDay($today),
                'occupied' => $occupied,
                'free' => $rooms->count() - $occupied,
                'status' => $status,
            ];

            if ($inMonth) {
                $schedule[$date->toDateString()] = $daily->groupBy('room_id');
            }
        }

        return compact('rooms', 'days', 'schedule');
    }
}
