<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RespondsWithMasterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Services\BookingManagement;
use Illuminate\Http\Request;

/**
 * Mengelola jadwal disetujui dan pembatalannya melalui master admin.
 * Konsep Laravel: Controller (MVC), Form Request dan Service Layer.
 */
class ScheduleManagementController extends Controller
{
    use RespondsWithMasterData;

    /**
     * Membuat jadwal langsung disetujui melalui service booking.
     * Konsep Laravel: Controller (MVC), Form Request dan Service Layer.
     */
    public function store(ScheduleRequest $request, BookingManagement $management)
    {
        $management->saveSchedule($request->validated(), $request->user()->id);

        return $this->saved($request, 'Jadwal berhasil dibuat dan langsung disetujui.', 'schedules');
    }

    /**
     * Memperbarui jadwal disetujui melalui service dengan pemeriksaan kapasitas dan bentrok.
     * Konsep Laravel: Controller (MVC), Form Request dan Service Layer.
     */
    public function update(ScheduleRequest $request, int $booking, BookingManagement $management)
    {
        $management->saveSchedule($request->validated(), $request->user()->id, $booking);

        return $this->saved($request, 'Jadwal berhasil diperbarui.', 'schedules');
    }

    /**
     * Membatalkan jadwal melalui service tanpa menghapus riwayat peminjaman.
     * Konsep Laravel: Controller (MVC), Form Request dan Service Layer.
     */
    public function cancel(Request $request, int $booking, BookingManagement $management)
    {
        $management->cancel($booking, $request->user()->id);

        return $this->saved($request, 'Jadwal dibatalkan. Riwayat tetap tersimpan.', 'schedules');
    }
}
