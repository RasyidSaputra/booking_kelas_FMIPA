<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
}
