<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RespondsWithMasterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class RoomManagementController extends Controller
{
    use RespondsWithMasterData;

    public function store(RoomRequest $request)
    {
        return $this->saveRoom($request);
    }

    public function update(RoomRequest $request, int $room)
    {
        return $this->saveRoom($request, $room);
    }

    private function saveRoom(RoomRequest $request, ?int $id = null)
    {
        $data = $request->validated();
        $newPhoto = $request->file('photo')?->store('rooms', 'public');
        if ($request->hasFile('photo') && ! $newPhoto) {
            throw ValidationException::withMessages(['photo' => 'Foto belum dapat disimpan. Coba kembali.']);
        }
        $oldPhoto = null;
        try {
            DB::transaction(function () use ($request, $data, $newPhoto, $id, &$oldPhoto): void {
                $existing = $id ? DB::table('rooms')->where('id', $id)->lockForUpdate()->first() : null;
                abort_if($id && ! $existing, 404);
                if ($id && DB::table('bookings')->where('room_id', $id)->where('status', 'approved')
                    ->where('ends_at', '>', now())->where('participant_count', '>', $data['capacity'])->exists()) {
                    throw ValidationException::withMessages(['capacity' => 'Kapasitas lebih kecil dari jumlah peserta pada jadwal yang sudah disetujui.']);
                }
                $values = collect($data)->only(['code', 'name', 'capacity', 'location', 'description', 'is_active'])->all();
                $values['updated_at'] = now();
                if ($newPhoto || $request->boolean('remove_photo')) {
                    $oldPhoto = $existing?->photo_path;
                    $values['photo_path'] = $newPhoto;
                }
                $roomId = $id;
                if ($id) {
                    DB::table('rooms')->where('id', $id)->update($values);
                } else {
                    $roomId = DB::table('rooms')->insertGetId($values + ['created_at' => now()]);
                }
                DB::table('facility_room')->where('room_id', $roomId)->delete();
                foreach ($data['facility_ids'] ?? [] as $facility) {
                    DB::table('facility_room')->insert(['room_id' => $roomId, 'facility_id' => $facility, 'created_at' => now(), 'updated_at' => now()]);
                }
            }, 3);
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }
            throw $exception;
        }
        if ($oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return $this->saved($request, $id ? 'Informasi ruangan berhasil diperbarui.' : 'Ruangan berhasil ditambahkan.', 'rooms');
    }

    public function destroy(Request $request, int $room)
    {
        $photo = DB::transaction(function () use ($room): ?string {
            $record = DB::table('rooms')->where('id', $room)->lockForUpdate()->first();
            abort_if(! $record, 404);
            if (DB::table('bookings')->where('room_id', $room)->exists()) {
                throw ValidationException::withMessages(['room_id' => 'Ruangan memiliki riwayat peminjaman. Nonaktifkan ruangan melalui form edit.']);
            }
            DB::table('rooms')->where('id', $room)->delete();

            return $record->photo_path;
        }, 3);
        if ($photo) {
            Storage::disk('public')->delete($photo);
        }

        return $this->saved($request, 'Ruangan berhasil dihapus.', 'rooms');
    }
}
