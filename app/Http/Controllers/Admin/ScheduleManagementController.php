<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RespondsWithMasterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Services\BookingManagement;
use Illuminate\Http\Request;

class ScheduleManagementController extends Controller
{
    use RespondsWithMasterData;

    public function store(ScheduleRequest $request, BookingManagement $management)
    {
        $management->saveSchedule($request->validated(), $request->user()->id);

        return $this->saved($request, 'Jadwal berhasil dibuat dan langsung disetujui.', 'schedules');
    }

    public function update(ScheduleRequest $request, int $booking, BookingManagement $management)
    {
        $management->saveSchedule($request->validated(), $request->user()->id, $booking);

        return $this->saved($request, 'Jadwal berhasil diperbarui.', 'schedules');
    }

    public function cancel(Request $request, int $booking, BookingManagement $management)
    {
        $management->cancel($booking, $request->user()->id);

        return $this->saved($request, 'Jadwal dibatalkan. Riwayat tetap tersimpan.', 'schedules');
    }
}
