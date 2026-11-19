<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Menerima permintaan peminjaman ruangan dan meneruskannya ke logika bisnis booking.
 * Konsep Laravel: Controller (MVC), HTTP Request dan Response.
 */
class BookingController extends Controller
{
    /**
     * Menampilkan daftar booking milik pengguna yang masuk dengan pagination.
     * Konsep Laravel: Controller (MVC), HTTP Request dan Response.
     */
    public function index(Request $request): View
    {
        $bookings = DB::table('bookings')->join('rooms', 'rooms.id', '=', 'bookings.room_id')
            ->where('bookings.user_id', $request->user()->id)
            ->orderByDesc('bookings.starts_at')
            ->select('bookings.*', 'rooms.name as room_name', 'rooms.code as room_code', 'rooms.capacity as room_capacity')
            ->paginate(10)->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Menampilkan detail booking setelah memeriksa keberadaan data dan akses pemilik atau admin.
     * Konsep Laravel: Controller (MVC), HTTP Request dan Response.
     */
    public function show(Request $request, int $booking): View
    {
        $bookingData = DB::table('bookings')
            ->join('rooms', 'rooms.id', '=', 'bookings.room_id')
            ->leftJoin('users as reviewer', 'reviewer.id', '=', 'bookings.reviewed_by')
            ->where('bookings.id', $booking)
            ->select(
                'bookings.*',
                'rooms.name as room_name',
                'rooms.code as room_code',
                'rooms.capacity as room_capacity',
                'rooms.location as room_location',
                'reviewer.name as reviewer_name'
            )
            ->first();

        abort_if($bookingData === null, 404);

        abort_unless(
            (int) $bookingData->user_id === (int) $request->user()->id
            || $request->user()->can('view-room-master'),
            403
        );

        return view('bookings.show', [
            'booking' => $bookingData,
        ]);
    }

    /**
     * Mengirim data tervalidasi ke service dan mengarahkan kembali dengan pesan sukses atau kesalahan.
     * Konsep Laravel: Controller (MVC), HTTP Request dan Response.
     */
    public function store(StoreBookingRequest $request, BookingManagement $management): RedirectResponse
    {
        try {
            $management->submit($request->validated(), $request->user()->id);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return back()->withInput()->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('bookings.index')->with('success', 'Pengajuan berhasil dibuat, silakan tunggu persetujuan Admin.');
    }

    /**
     * Memvalidasi revisi booking lalu meminta service memperbarui jadwal milik pengguna.
     * Konsep Laravel: Controller (MVC), HTTP Request dan Response.
     */
    public function update(Request $request, int $booking, BookingManagement $management): RedirectResponse
    {
        $validated = $request->validate([
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i', 'after_or_equal:07:30', 'before:18:00'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', 'before_or_equal:18:00'],
            'purpose' => ['required', 'string', 'max:500'],
            'participant_count' => ['nullable', 'integer', 'min:1', 'max:200'],
        ], [
            'booking_date.after_or_equal' => 'Tanggal peminjaman tidak boleh tanggal yang sudah lewat.',
            'start_time.after_or_equal' => 'Jadwal penggunaan ruangan dimulai dari pukul 07:30 WIB.',
            'start_time.before' => 'Waktu mulai peminjaman harus sebelum pukul 18:00 WIB.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'end_time.before_or_equal' => 'Gedung tidak bisa digunakan setelah pukul 18:00 WIB.',
            'purpose.required' => 'Tujuan peminjaman wajib diisi.',
        ]);

        try {
            $requiresApproval = $management->reviseForOwner($booking, $validated, $request->user()->id);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return back()->withInput()->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('bookings.index')->with('success', $requiresApproval
            ? 'Waktu booking berhasil direvisi dan diajukan ulang untuk persetujuan Admin.'
            : 'Waktu booking berhasil direvisi.');
    }

    /**
     * Meminta service membatalkan booking milik pengguna dan menampilkan hasilnya melalui session flash.
     * Konsep Laravel: Controller (MVC), HTTP Request dan Response.
     */
    public function cancel(Request $request, int $booking, BookingManagement $management): RedirectResponse
    {
        try {
            $management->cancelForOwner($booking, $request->user()->id);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil dibatalkan.');
    }

    /**
     * Menggunakan kembali alur pembatalan agar permintaan DELETE tetap mempertahankan riwayat booking.
     * Konsep Laravel: Controller (MVC), HTTP Request dan Response.
     */
    public function destroy(Request $request, int $booking, BookingManagement $management): RedirectResponse
    {
        return $this->cancel($request, $booking, $management);
    }
}
