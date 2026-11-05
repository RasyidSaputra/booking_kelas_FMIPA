<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $rooms = DB::table('rooms')->where('is_active', true)
            ->when($data['q'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%')))
            ->when($data['capacity'] ?? null, fn ($query, $capacity) => $query->where('capacity', '>=', $capacity))
            ->orderBy('id')->paginate($data['per_page'] ?? 15)->withQueryString();

        return RoomResource::collection($rooms);
    }

    public function show(int $room): RoomResource
    {
        return new RoomResource($this->findRoom($room));
    }

    public function availability(Request $request, int $room)
    {
        $record = $this->findRoom($room);
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i', 'after_or_equal:07:30', 'before:18:00'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time', 'before_or_equal:18:00'],
        ]);
        $startsAt = CarbonImmutable::parse($data['date'].' '.$data['start_time']);
        $endsAt = CarbonImmutable::parse($data['date'].' '.$data['end_time']);
        $conflict = DB::table('bookings')->where('room_id', $room)->where('status', 'approved')
            ->where('starts_at', '<', $endsAt->toDateTimeString())->where('ends_at', '>', $startsAt->toDateTimeString())->exists();

        return response()->json(['data' => [
            'room_id' => $record->id, 'starts_at' => $startsAt->toIso8601String(), 'ends_at' => $endsAt->toIso8601String(),
            'available' => $startsAt->isFuture() && ! $conflict,
            'notice' => 'Wajib memiliki surat izin atau surat peminjaman ruangan. Pengajuan tetap memerlukan persetujuan admin; ketersediaan diperiksa ulang saat pengajuan dan persetujuan.',
        ]]);
    }

    private function findRoom(int $id): object
    {
        $room = DB::table('rooms')->where('is_active', true)->find($id);
        abort_if(! $room, 404);

        return $room;
    }
}
