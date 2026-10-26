<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class RoomMasterController extends Controller
{
    public function __invoke(Request $request): View
    {
        $input = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tab' => ['nullable', Rule::in(['rooms', 'facilities', 'bookings', 'schedules', 'users'])],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive', 'pending', 'approved', 'rejected', 'cancelled'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $search = trim($input['q'] ?? '');
        $tab = $input['tab'] ?? 'rooms';
        $status = $input['status'] ?? ($tab === 'bookings' ? 'pending' : 'all');
        $date = CarbonImmutable::parse($input['date'] ?? today()->toDateString())->locale('id');
        $query = match ($tab) {
            'users' => DB::table('users')->select('id', 'name', 'identity_number', 'email', 'role', 'created_at')->orderBy('name'),
            'facilities' => DB::table('facilities')->orderBy('name'),
            'bookings', 'schedules' => DB::table('bookings')->join('rooms', 'rooms.id', '=', 'bookings.room_id')->join('users', 'users.id', '=', 'bookings.user_id')
                ->select('bookings.*', 'rooms.name as room_name', 'rooms.code as room_code', 'users.name as user_name', 'users.identity_number')
                ->orderByDesc('bookings.starts_at'),
            default => DB::table('rooms')->orderBy('name'),
        };
        $columns = match ($tab) {
            'users' => ['name', 'identity_number', 'email'],
            'facilities' => ['name'],
            'bookings', 'schedules' => ['rooms.name', 'rooms.code', 'users.name', 'users.identity_number', 'bookings.purpose'],
            default => ['name', 'code', 'location'],
        };
        if ($search !== '') {
            $query->where(function ($query) use ($search, $columns): void {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }
        if ($tab === 'rooms' && in_array($status, ['active', 'inactive'])) {
            $query->where('is_active', $status === 'active');
        }
        if ($tab === 'bookings' && in_array($status, ['pending', 'approved', 'rejected', 'cancelled'])) {
            $query->where('bookings.status', $status);
        }
        if ($tab === 'schedules') {
            $query->where('bookings.status', 'approved')->where('starts_at', '<', $date->addDay()->toDateTimeString())->where('ends_at', '>', $date->toDateTimeString());
        }
        $items = $query->paginate(15)->withQueryString();
        $pivots = DB::table('facility_room')->get(['room_id', 'facility_id'])->groupBy('room_id');
        foreach ($items as $item) {
            if ($tab === 'rooms') {
                $item->facility_ids = $pivots->get($item->id, collect())->pluck('facility_id')->all();
                $item->photo_url = $item->photo_path ? Storage::disk('public')->url($item->photo_path) : null;
            }
        }
        $roomOptions = DB::table('rooms')->orderBy('name')->get(['id', 'code', 'name', 'capacity', 'is_active']);
        $facilityOptions = DB::table('facilities')->orderBy('name')->get(['id', 'name']);
        $userOptions = DB::table('users')->orderBy('name')->get(['id', 'name', 'identity_number', 'role']);
        $stats = [
            'rooms' => DB::table('rooms')->count(),
            'pending' => DB::table('bookings')->where('status', 'pending')->count(),
            'schedules' => DB::table('bookings')->where('status', 'approved')->where('ends_at', '>', now())->count(),
            'users' => DB::table('users')->count(),
        ];

        return view('admin.rooms.index', compact('items', 'search', 'tab', 'status', 'date', 'roomOptions', 'facilityOptions', 'userOptions', 'stats'));
    }
}
