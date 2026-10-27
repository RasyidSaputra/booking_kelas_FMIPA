<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $input = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $search = trim($input['q'] ?? '');
        $date = CarbonImmutable::parse($input['date'] ?? today()->toDateString())->locale('id');
        $rooms = DB::table('rooms')
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')->paginate(12)->withQueryString();
        $schedule = DB::table('bookings')->where('status', 'approved')
            ->whereIn('room_id', $rooms->pluck('id'))
            ->where('starts_at', '<', $date->addDay()->toDateTimeString())
            ->where('ends_at', '>', $date->toDateTimeString())
            ->orderBy('starts_at')->get(['room_id', 'starts_at', 'ends_at'])->groupBy('room_id');

        return view('rooms.index', compact('rooms', 'search', 'date', 'schedule'));
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room): View
    {
        $now = now();
        $currentBooking = DB::table('bookings')
            ->join('users', 'users.id', '=', 'bookings.user_id')
            ->where('room_id', $room->id)
            ->where('status', 'approved')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
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
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        //
    }
}
