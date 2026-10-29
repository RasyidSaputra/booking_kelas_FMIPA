<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
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

        $startsAt = Carbon::parse("{$validated['booking_date']} {$validated['start_time']}");
        $endsAt = Carbon::parse("{$validated['booking_date']} {$validated['end_time']}");

        // Validasi jika jam mulai sudah lewat pada hari ini
        if ($startsAt->isPast()) {
            return back()->withInput()->with('error', 'Waktu mulai peminjaman sudah lewat.');
        }

        return DB::transaction(function () use ($request, $validated, $startsAt, $endsAt): RedirectResponse {
            $room = DB::table('rooms')->where('id', $validated['room_id'])->lockForUpdate()->first();
            if (! $room || ! $room->is_active) {
                return back()->withInput()->with('error', 'Ruangan ini saat ini tidak aktif.');
            }
            if (($validated['participant_count'] ?? $room->capacity) > $room->capacity) {
                return back()->withInput()->with('error', 'Jumlah peserta melebihi kapasitas ruangan.');
            }

            // Pengecekan bentrok jadwal dengan booking yang berstatus 'approved'
            $hasConflict = DB::table('bookings')
                ->where('room_id', $validated['room_id'])
                ->where('status', 'approved')
                ->where(function ($query) use ($startsAt, $endsAt): void {
                    $query->where('starts_at', '<', $endsAt->toDateTimeString())
                        ->where('ends_at', '>', $startsAt->toDateTimeString());
                })
                ->lockForUpdate()->first(['id']);

            if ($hasConflict) {
                return back()->withInput()->with('error', 'Maaf, waktu tersebut bertabrakan dengan jadwal kuliah atau peminjaman yang sudah disetujui.');
            }

            // Simpan peminjaman baru ke database
            DB::table('bookings')->insert([
                'user_id' => $request->user()->id,
                'room_id' => $validated['room_id'],
                'purpose' => $validated['purpose'],
                'participant_count' => $validated['participant_count'] ?? $room->capacity,
                'starts_at' => $startsAt->toDateTimeString(),
                'ends_at' => $endsAt->toDateTimeString(),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('bookings.index')->with('success', 'Pengajuan berhasil dibuat, silakan tunggu persetujuan Admin.');
        }, 3);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $booking): \Illuminate\Http\RedirectResponse
    {
        $item = DB::table('bookings')->where('id', $booking)->first();

        if (! $item) {
            abort(404, 'Booking tidak ditemukan.');
        }

        if ($item->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah booking ini.');
        }

        if (! in_array($item->status, ['pending', 'approved'])) {
            return back()->with('error', 'Booking dengan status ini tidak dapat direvisi.');
        }

        if (\Carbon\Carbon::parse($item->ends_at)->isPast()) {
            return back()->with('error', 'Booking yang sudah selesai tidak dapat direvisi.');
        }

        $validated = $request->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
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

        $startsAt = \Carbon\Carbon::parse("{$validated['booking_date']} {$validated['start_time']}");
        $endsAt = \Carbon\Carbon::parse("{$validated['booking_date']} {$validated['end_time']}");

        if ($startsAt->isPast()) {
            return back()->withInput()->with('error', 'Waktu mulai peminjaman sudah lewat.');
        }

        $room = DB::table('rooms')->where('id', $item->room_id)->first();
        if (! $room || ! $room->is_active) {
            return back()->withInput()->with('error', 'Ruangan ini saat ini tidak aktif.');
        }

        $participantCount = $validated['participant_count'] ?? $item->participant_count;
        if ($participantCount > $room->capacity) {
            return back()->withInput()->with('error', "Jumlah peserta melebihi kapasitas ruangan ({$room->capacity} orang).");
        }

        // Pengecekan bentrok jadwal dengan booking lain yang berstatus 'approved'
        $hasConflict = DB::table('bookings')
            ->where('room_id', $item->room_id)
            ->where('status', 'approved')
            ->where('id', '!=', $item->id)
            ->where(function ($query) use ($startsAt, $endsAt): void {
                $query->where('starts_at', '<', $endsAt->toDateTimeString())
                    ->where('ends_at', '>', $startsAt->toDateTimeString());
            })
            ->exists();

        if ($hasConflict) {
            return back()->withInput()->with('error', 'Maaf, ruangan sudah dipesan pada jam tersebut.');
        }

        $updateData = [
            'purpose' => $validated['purpose'],
            'participant_count' => $participantCount,
            'starts_at' => $startsAt->toDateTimeString(),
            'ends_at' => $endsAt->toDateTimeString(),
            'updated_at' => now(),
        ];

        if ($item->status === 'approved') {
            $updateData['status'] = 'pending';
            $updateData['reviewed_by'] = null;
            $updateData['reviewed_at'] = null;
            $updateData['rejection_reason'] = null;
            $successMessage = 'Waktu booking berhasil direvisi dan diajukan ulang untuk persetujuan Admin.';
        } else {
            $successMessage = 'Waktu booking berhasil direvisi.';
        }

        DB::table('bookings')->where('id', $item->id)->update($updateData);

        return redirect()->route('bookings.index')->with('success', $successMessage);
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Request $request, int $booking): \Illuminate\Http\RedirectResponse
    {
        $item = DB::table('bookings')->where('id', $booking)->first();

        if (! $item) {
            abort(404, 'Booking tidak ditemukan.');
        }

        if ($item->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan booking ini.');
        }

        if (! in_array($item->status, ['pending', 'approved'])) {
            return back()->with('error', 'Booking dengan status ini tidak dapat dibatalkan.');
        }

        if (\Carbon\Carbon::parse($item->ends_at)->isPast()) {
            return back()->with('error', 'Booking yang sudah selesai tidak dapat dibatalkan.');
        }

        DB::table('bookings')->where('id', $item->id)->update([
            'status' => 'cancelled',
            'updated_at' => now(),
        ]);

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil dibatalkan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $booking): \Illuminate\Http\RedirectResponse
    {
        return $this->cancel($request, $booking);
    }
}
