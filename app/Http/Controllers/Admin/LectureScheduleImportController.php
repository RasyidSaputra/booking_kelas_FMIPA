<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LectureScheduleCsvImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LectureScheduleImportController extends Controller
{
    private const SESSION_KEY = 'lecture_schedule_import';

    public function index(Request $request, LectureScheduleCsvImport $import): View
    {
        $pending = $request->session()->get(self::SESSION_KEY);
        if ($pending && ($pending['actor'] !== $request->user()->id || $pending['expires_at'] < now()->timestamp)) {
            $request->session()->forget(self::SESSION_KEY);
            $pending = null;
        }
        $preview = $pending ? $import->inspect($pending['rows']) : null;

        return view('admin.lecture-schedules.import', compact('pending', 'preview'));
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, LectureScheduleCsvImport::HEADERS, ',', '"', '');
            fputcsv($handle, [
                'Ganjil 2026/2027', 'FMIPA-101', 'NIP_DOSEN_TERDAFTAR', 'Kalkulus - Kelas A',
                'Senin', '08:00', '10:00', '40', today()->toDateString(), today()->addMonths(4)->toDateString(),
            ], ',', '"', '');
            fclose($handle);
        }, 'template-jadwal-kuliah.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function preview(Request $request, LectureScheduleCsvImport $import): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->validate(['csv_file' => ['required', 'file', 'extensions:csv', 'mimes:csv,txt', 'max:1024']], [
            'csv_file.required' => 'Pilih file CSV jadwal kuliah.',
            'csv_file.extensions' => 'Gunakan file .csv. Dari Excel, simpan sebagai CSV UTF-8.',
            'csv_file.mimes' => 'File harus berupa CSV teks. Dari Excel, simpan sebagai CSV UTF-8.',
            'csv_file.max' => 'Ukuran file CSV maksimal 1 MB.',
        ]);
        $rows = $import->read($request->file('csv_file'));
        $request->session()->put(self::SESSION_KEY, [
            'actor' => $request->user()->id, 'token' => Str::random(40),
            'expires_at' => now()->addMinutes(30)->timestamp, 'rows' => $rows,
        ]);

        return to_route('admin.lecture-schedules.import.index');
    }

    public function confirm(Request $request, LectureScheduleCsvImport $import): RedirectResponse
    {
        $request->validate(['token' => ['required', 'string', 'size:40']]);
        $pending = $request->session()->get(self::SESSION_KEY);
        if (! $pending || $pending['actor'] !== $request->user()->id || $pending['expires_at'] < now()->timestamp
            || ! hash_equals($pending['token'], $request->string('token')->toString())) {
            throw ValidationException::withMessages(['csv_file' => 'Pratinjau tidak tersedia atau sudah kedaluwarsa. Unggah ulang CSV dan periksa sebelum menyimpan.']);
        }
        $result = $import->commit($pending['rows'], $request->user()->id);
        $request->session()->forget(self::SESSION_KEY);

        return to_route('admin.lecture-schedules.index')->with('master_success',
            "Impor selesai: {$result['created']} pola jadwal dan {$result['meetings']} pertemuan dibuat. {$result['duplicate']} baris duplikat dilewati."
        );
    }
}
