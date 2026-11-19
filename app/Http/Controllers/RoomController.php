<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Menyajikan daftar, informasi, dan jadwal pemakaian ruangan.
 * Konsep Laravel: Controller (MVC), Query Builder dan View.
 */
class RoomController extends Controller
{
    /**
     * Memvalidasi filter dan menyajikan ruangan aktif beserta jadwal serta ringkasan ketersediaannya.
     * Konsep Laravel: Controller (MVC), Query Builder dan View.
     */
    public function index(Request $request): View
    {
        $input = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'floor' => ['nullable', 'string', 'in:all,1,2,3'],
            'capacity' => ['nullable', 'integer', 'in:0,40,50,60'],
            'availability' => ['nullable', 'string', 'in:all,free'],
        ]);
        $search = trim($input['q'] ?? '');
        $date = CarbonImmutable::parse($input['date'] ?? today()->toDateString())->locale('id');
        $floor = $input['floor'] ?? 'all';
        $capacity = (int) ($input['capacity'] ?? 0);
        $availability = $input['availability'] ?? 'all';

        $query = DB::table('rooms')->where('is_active', true);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%');
            });
        }

        if ($floor !== 'all' && in_array($floor, ['1', '2', '3'])) {
            $query->where(function ($q) use ($floor): void {
                $q->where('location', 'like', '%Lantai '.$floor.'%')
                    ->orWhere('code', 'like', 'FMIPA-'.$floor.'%');
            });
        }

        if ($capacity > 0) {
            $query->where('capacity', '>=', $capacity);
        }

        if ($availability === 'free') {
            $occupiedRoomIds = DB::table('bookings')
                ->where('status', 'approved')
                ->where('starts_at', '<', $date->addDay()->toDateTimeString())
                ->where('ends_at', '>', $date->toDateTimeString())
                ->pluck('room_id')
                ->unique();

            $query->whereNotIn('id', $occupiedRoomIds);
        }

        $rooms = $query->orderBy('name')->paginate(12)->withQueryString();

        $schedule = DB::table('bookings')->where('status', 'approved')
            ->whereIn('room_id', $rooms->pluck('id'))
            ->where('starts_at', '<', $date->addDay()->toDateTimeString())
            ->where('ends_at', '>', $date->toDateTimeString())
            ->orderBy('starts_at')
            ->get(['room_id', 'purpose', 'starts_at', 'ends_at'])
            ->groupBy('room_id');

        $now = now();
        $isToday = $date->isToday();

        $currentlyOccupiedCount = $isToday
            ? DB::table('bookings')
                ->where('status', 'approved')
                ->where('starts_at', '<=', $now->toDateTimeString())
                ->where('ends_at', '>', $now->toDateTimeString())
                ->distinct('room_id')
                ->count('room_id')
            : 0;

        $totalActiveRooms = DB::table('rooms')->where('is_active', true)->count();
        $currentlyAvailableCount = max(0, $totalActiveRooms - $currentlyOccupiedCount);

        return view('rooms.index', compact(
            'rooms', 'search', 'date', 'schedule', 'floor', 'capacity',
            'availability', 'isToday', 'currentlyOccupiedCount', 'currentlyAvailableCount'
        ));
    }

    /**
     * Menyediakan kerangka aksi create; method ini belum berisi implementasi.
     * Konsep Laravel: Controller (MVC), Query Builder dan View.
     */
    public function create()
    {
        //
    }

    /**
     * Menyediakan kerangka aksi store; method ini belum berisi implementasi.
     * Konsep Laravel: Controller (MVC), Query Builder dan View.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Menampilkan informasi ruangan hasil route model binding, fasilitas, dan jadwal yang sudah disetujui.
     * Konsep Laravel: Controller (MVC), Query Builder dan View.
     */
    public function show(Room $room): View
    {
        $now = now();
        $currentBooking = DB::table('bookings')
            ->join('users', 'users.id', '=', 'bookings.user_id')
            ->where('room_id', $room->id)
            ->where('status', 'approved')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now)
            ->select('bookings.*', 'users.name as user_name')
            ->orderBy('ends_at')
            ->first();

        $todayBookings = DB::table('bookings')
            ->join('users', 'users.id', '=', 'bookings.user_id')
            ->where('room_id', $room->id)
            ->where('status', 'approved')
            ->whereDate('starts_at', today())
            ->select('bookings.*', 'users.name as user_name')
            ->orderBy('starts_at')
            ->get();

        $facilities = DB::table('facilities')
            ->join('facility_room', 'facilities.id', '=', 'facility_room.facility_id')
            ->where('facility_room.room_id', $room->id)
            ->select('facilities.*')
            ->get();

        return view('rooms.show', compact('room', 'currentBooking', 'todayBookings', 'facilities'));
    }

    /**
     * Menyediakan kerangka aksi edit; method ini belum berisi implementasi.
     * Konsep Laravel: Controller (MVC), Query Builder dan View.
     */
    public function edit(Room $room)
    {
        //
    }

    /**
     * Menyediakan kerangka aksi update; method ini belum berisi implementasi.
     * Konsep Laravel: Controller (MVC), Query Builder dan View.
     */
    public function update(Request $request, Room $room)
    {
        //
    }

    /**
     * Menyediakan kerangka aksi destroy; method ini belum berisi implementasi.
     * Konsep Laravel: Controller (MVC), Query Builder dan View.
     */
    public function destroy(Room $room)
    {
        //
    }
}
