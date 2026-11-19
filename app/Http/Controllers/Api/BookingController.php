<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Menerima permintaan peminjaman ruangan dan meneruskannya ke logika bisnis booking.
 * Konsep Laravel: API Controller, Query Builder, Pagination dan API Resource.
 */
class BookingController extends Controller
{
    /**
     * Mengembalikan daftar booking pengguna yang masuk melalui alur listing bersama.
     * Konsep Laravel: API Controller, API Resource dan Query Builder.
     */
    public function index(Request $request)
    {
        return $this->listing($request, false);
    }

    /**
     * Mengembalikan daftar seluruh booking untuk endpoint admin yang dilindungi Gate.
     * Konsep Laravel: API Controller, API Resource dan Query Builder.
     */
    public function adminIndex(Request $request)
    {
        return $this->listing($request, true);
    }

    /**
     * Memvalidasi filter dan pagination lalu mengubah hasil Query Builder menjadi koleksi BookingResource.
     * Konsep Laravel: API Controller, API Resource dan Query Builder.
     */
    private function listing(Request $request, bool $all)
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'cancelled'])],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $query = DB::table('bookings')->when(! $all, fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status));

        return BookingResource::collection($query->orderByDesc('id')->paginate($data['per_page'] ?? 15)->withQueryString());
    }

    /**
     * Memeriksa akses pemilik atau admin sebelum mengembalikan satu booking sebagai JSON resource.
     * Konsep Laravel: API Controller, API Resource dan Query Builder.
     */
    public function show(Request $request, int $booking): BookingResource
    {
        $record = DB::table('bookings')->find($booking);
        abort_if(! $record, 404);
        abort_unless($record->user_id === $request->user()->id || $request->user()->can('view-room-master'), 403);

        return new BookingResource($record);
    }

    /**
     * Menyimpan pengajuan melalui service lalu mengembalikan BookingResource dengan status HTTP 201.
     * Konsep Laravel: API Controller, API Resource dan Query Builder.
     */
    public function store(StoreBookingRequest $request, BookingManagement $management)
    {
        $id = $management->submit($request->validated(), $request->user()->id);

        return (new BookingResource(DB::table('bookings')->find($id)))->response()->setStatusCode(201);
    }

    /**
     * Memproses keputusan admin melalui service lalu mengembalikan data booking terbaru.
     * Konsep Laravel: API Controller, API Resource dan Query Builder.
     */
    public function review(ReviewBookingRequest $request, int $booking, BookingManagement $management): BookingResource
    {
        $data = $request->validated();
        $management->review($booking, $data['decision'], $data['rejection_reason'] ?? null, $request->user()->id);

        return new BookingResource(DB::table('bookings')->find($booking));
    }
}
