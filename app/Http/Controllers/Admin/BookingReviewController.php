<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\RespondsWithMasterData;
use App\Http\Requests\ReviewBookingRequest;
use App\Services\BookingManagement;

/**
 * Menangani satu aksi pemeriksaan pengajuan booking oleh administrator.
 * Konsep Laravel: Invokable Controller, Form Request dan Service Layer.
 */
class BookingReviewController extends Controller
{
    use RespondsWithMasterData;

    /**
     * Meneruskan keputusan tervalidasi ke service booking lalu mengembalikan respons master data.
     * Konsep Laravel: Invokable Controller, Form Request dan Service Layer.
     */
    public function __invoke(ReviewBookingRequest $request, int $booking, BookingManagement $management)
    {
        $data = $request->validated();
        $management->review($booking, $data['decision'], $data['rejection_reason'] ?? null, $request->user()->id);

        return $this->saved($request, $data['decision'] === 'approved' ? 'Pengajuan berhasil disetujui.' : 'Pengajuan berhasil ditolak.', 'bookings');
    }
}
