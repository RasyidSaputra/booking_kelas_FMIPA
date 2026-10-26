<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\RespondsWithMasterData;
use App\Http\Requests\ReviewBookingRequest;
use App\Services\BookingManagement;

class BookingReviewController extends Controller
{
    use RespondsWithMasterData;

    public function __invoke(ReviewBookingRequest $request, int $booking, BookingManagement $management)
    {
        $data = $request->validated();
        $management->review($booking, $data['decision'], $data['rejection_reason'] ?? null, $request->user()->id);

        return $this->saved($request, $data['decision'] === 'approved' ? 'Pengajuan berhasil disetujui.' : 'Pengajuan berhasil ditolak.', 'bookings');
    }
}
