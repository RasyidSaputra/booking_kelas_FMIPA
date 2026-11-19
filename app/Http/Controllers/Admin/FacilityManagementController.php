<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\RespondsWithMasterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacilityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mengelola tambah, ubah, dan hapus fasilitas melalui halaman master admin.
 * Konsep Laravel: Controller (MVC), Form Request dan Query Builder.
 */
class FacilityManagementController extends Controller
{
    use RespondsWithMasterData;

    /**
     * Menyimpan fasilitas baru dari input yang sudah divalidasi.
     * Konsep Laravel: Controller (MVC), Form Request dan Query Builder.
     */
    public function store(FacilityRequest $request)
    {
        DB::table('facilities')->insert($request->validated() + ['created_at' => now(), 'updated_at' => now()]);

        return $this->saved($request, 'Fasilitas berhasil ditambahkan.', 'facilities');
    }

    /**
     * Memastikan fasilitas ada lalu memperbarui nama dari input tervalidasi.
     * Konsep Laravel: Controller (MVC), Form Request dan Query Builder.
     */
    public function update(FacilityRequest $request, int $facility)
    {
        abort_unless(DB::table('facilities')->where('id', $facility)->exists(), 404);
        DB::table('facilities')->where('id', $facility)->update($request->validated() + ['updated_at' => now()]);

        return $this->saved($request, 'Fasilitas berhasil diperbarui.', 'facilities');
    }

    /**
     * Mengunci fasilitas dan menolak penghapusan jika masih terhubung dengan ruangan.
     * Konsep Laravel: Controller (MVC), Form Request dan Query Builder.
     */
    public function destroy(Request $request, int $facility)
    {
        DB::transaction(function () use ($facility): void {
            $record = DB::table('facilities')->where('id', $facility)->lockForUpdate()->first();
            abort_if(! $record, 404);
            if (DB::table('facility_room')->where('facility_id', $facility)->exists()) {
                throw ValidationException::withMessages(['facility_ids' => 'Fasilitas masih digunakan ruangan. Lepaskan dari ruangan sebelum menghapus.']);
            }
            DB::table('facilities')->where('id', $facility)->delete();
        }, 3);

        return $this->saved($request, 'Fasilitas berhasil dihapus.', 'facilities');
    }
}
