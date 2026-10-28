<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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
            ->select('bookings.*', 'rooms.name as room_name', 'rooms.code as room_code')
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
    public function store(Request $request): \Illuminate\Http\RedirectResponse
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

        $startsAt = \Carbon\Carbon::parse("{$validated['booking_date']} {$validated['start_time']}");
        $endsAt = \Carbon\Carbon::parse("{$validated['booking_date']} {$validated['end_time']}");

        // Validasi jika jam mulai sudah lewat pada hari ini
        if ($startsAt->isPast()) {
            return back()->withInput()->with('error', 'Waktu mulai peminjaman sudah lewat.');
        }

        $room = DB::table('rooms')->where('id', $validated['room_id'])->first();
        if (! $room || ! $room->is_active) {
            return back()->withInput()->with('error', 'Ruangan ini saat ini tidak aktif.');
        }

        // Pengecekan bentrok jadwal dengan booking yang berstatus 'approved'
        $hasConflict = DB::table('bookings')
            ->where('room_id', $validated['room_id'])
            ->where('status', 'approved')
            ->where(function ($query) use ($startsAt, $endsAt): void {
                $query->where('starts_at', '<', $endsAt->toDateTimeString())
                    ->where('ends_at', '>', $startsAt->toDateTimeString());
            })
            ->exists();

        if ($hasConflict) {
            return back()->withInput()->with('error', 'Maaf, ruangan sudah dipesan pada jam tersebut.');
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
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
