<?php

namespace App\Http\Controllers;

use App\Services\RoomCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, RoomCalendar $calendar): View
    {
        $input = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $today = CarbonImmutable::today();
        $month = CarbonImmutable::create((int) ($input['year'] ?? $today->year), (int) ($input['month'] ?? $today->month), 1)->startOfDay()->locale('id');
        $selectedDate = isset($input['date']) ? CarbonImmutable::parse($input['date'])->locale('id') : $today->locale('id');

        if (! $selectedDate->isSameMonth($month)) {
            $selectedDate = $month;
        }

        $calendarData = $calendar->forMonth($month);
        $selectedSchedule = $calendarData['schedule'][$selectedDate->toDateString()];
        $myBookings = DB::table('bookings')->where('user_id', $request->user()->id);
        $activeBookings = (clone $myBookings)->whereIn('status', ['pending', 'approved'])->where('ends_at', '>', now())->count();
        $pendingBookings = (clone $myBookings)->where('status', 'pending')->count();
        $months = collect(range(1, 12))->mapWithKeys(fn (int $number): array => [$number => CarbonImmutable::create(2026, $number, 1)->locale('id')->translatedFormat('F')]);

        return view('dashboard', array_merge($calendarData, compact('month', 'selectedDate', 'selectedSchedule', 'activeBookings', 'pendingBookings', 'months')));
    }
}
