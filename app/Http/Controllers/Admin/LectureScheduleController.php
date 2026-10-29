<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LectureScheduleRequest;
use App\Services\LectureScheduleManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LectureScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $input = $request->validate(['edit' => ['nullable', 'integer', 'exists:lecture_schedules,id']]);
        $editing = isset($input['edit']) ? DB::table('lecture_schedules')->find($input['edit']) : null;
        $schedules = DB::table('lecture_schedules')->join('rooms', 'rooms.id', '=', 'lecture_schedules.room_id')
            ->join('users', 'users.id', '=', 'lecture_schedules.user_id')
            ->select('lecture_schedules.*', 'rooms.name as room_name', 'rooms.code as room_code', 'users.name as user_name')
            ->orderByDesc('lecture_schedules.ends_on')->orderBy('lecture_schedules.weekday')->paginate(15)->withQueryString();
        $rooms = DB::table('rooms')->orderBy('name')->get(['id', 'name', 'code', 'capacity', 'is_active']);
        $users = DB::table('users')->whereIn('role', ['admin', 'dosen'])->orderBy('name')->get(['id', 'name']);

        return view('admin.lecture-schedules.index', compact('schedules', 'editing', 'rooms', 'users'));
    }

    public function store(LectureScheduleRequest $request, LectureScheduleManagement $management): RedirectResponse
    {
        $management->save($request->validated(), $request->user()->id);

        return to_route('admin.lecture-schedules.index')->with('master_success', 'Jadwal kuliah mingguan berhasil dibuat. Seluruh pertemuan mendatang otomatis memblokir peminjaman.');
    }

    public function update(LectureScheduleRequest $request, int $lectureSchedule, LectureScheduleManagement $management): RedirectResponse
    {
        $management->save($request->validated(), $request->user()->id, $lectureSchedule);

        return to_route('admin.lecture-schedules.index')->with('master_success', 'Pertemuan mendatang berhasil diperbarui. Riwayat dan pengecualian pertemuan tetap disimpan.');
    }

    public function cancel(Request $request, int $lectureSchedule, LectureScheduleManagement $management): RedirectResponse
    {
        $data = $request->validate(['effective_from' => ['required', 'date_format:Y-m-d', 'after_or_equal:today']]);
        $management->cancel($lectureSchedule, $data['effective_from'], $request->user()->id);

        return to_route('admin.lecture-schedules.index')->with('master_success', 'Jadwal kuliah dihentikan mulai tanggal pilihan. Riwayat pertemuan sebelumnya tetap tersimpan.');
    }
}
