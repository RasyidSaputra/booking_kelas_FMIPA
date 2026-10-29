<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BookingManagement;
use App\Services\LectureScheduleCsvImport;
use App\Services\LectureScheduleManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LectureScheduleImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $lecturer;

    private int $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(9, 0));
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->lecturer = User::factory()->create(['role' => 'dosen', 'identity_number' => '001234567890']);
        $this->room = DB::table('rooms')->insertGetId([
            'code' => 'CSV-101', 'name' => 'Ruang Impor Jadwal', 'capacity' => 50,
            'location' => 'Gedung Kuliah', 'is_active' => true,
        ]);
        $this->actingAs($this->admin);
    }

    public function test_admin_downloads_a_csv_template_and_can_open_the_import_form(): void
    {
        $this->get('/admin/lecture-schedules')->assertOk()->assertSeeText('Impor CSV');
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSeeText('Unduh template CSV');
        $response = $this->get('/admin/lecture-schedules/import/template')->assertOk()->assertDownload('template-jadwal-kuliah.csv');
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF".implode(',', LectureScheduleCsvImport::HEADERS), $content);
        $this->assertStringContainsString('NIP_DOSEN_TERDAFTAR', $content);
    }

    public function test_preview_is_read_only_and_confirmation_imports_multiple_weekly_patterns(): void
    {
        $token = $this->upload([$this->row(), $this->row(['hari' => 'Selasa', 'mata_kuliah_kelas' => 'Fisika - Kelas B'])]);
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSeeText('2 siap impor')
            ->assertSeeText('7 pertemuan baru')->assertSeeText('001234567890');
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 0);
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token, 'purpose' => 'Isian yang tidak dipercaya'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.lecture-schedules.index'))
            ->assertSessionHas('master_success', 'Impor selesai: 2 pola jadwal dan 7 pertemuan dibuat. 0 baris duplikat dilewati.');
        $this->assertDatabaseCount('lecture_schedules', 2);
        $this->assertDatabaseCount('bookings', 7);
        $this->assertDatabaseHas('lecture_schedules', ['purpose' => 'Kalkulus - Kelas A', 'user_id' => $this->lecturer->id]);
        $this->assertSame(7, DB::table('bookings')->where('status', 'approved')->count());
        $this->assertNull(session('lecture_schedule_import'));
    }

    public function test_duplicates_in_one_file_and_subsequent_uploads_do_not_multiply_schedules(): void
    {
        $rows = [$this->row(), $this->row(['kode_ruangan' => 'csv-101', 'hari' => '1', 'mulai' => '8:00:00', 'mata_kuliah_kelas' => ' kalkulus   - kelas a '])];
        $token = $this->upload($rows);
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSeeText('1 siap impor')->assertSeeText('1 duplikat');
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasNoErrors();
        $before = DB::table('bookings')->orderBy('id')->get()->toJson();
        $token = $this->upload($rows);
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSeeText('0 siap impor')->assertSeeText('2 duplikat');
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('lecture_schedules', 1);
        $this->assertSame($before, DB::table('bookings')->orderBy('id')->get()->toJson());
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseCount('bookings', 4);
    }

    public function test_duplicates_are_also_detected_for_patterns_created_manually_and_later_stopped(): void
    {
        $this->post('/admin/lecture-schedules', [
            'room_id' => $this->room, 'user_id' => $this->lecturer->id,
            'semester' => 'Ganjil 2026/2027', 'purpose' => 'Kalkulus - Kelas A',
            'weekday' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'participant_count' => 40,
            'starts_on' => '2026-11-02', 'ends_on' => '2026-11-23',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.lecture-schedules.index'));
        $id = DB::table('lecture_schedules')->value('id');
        $this->patch('/admin/lecture-schedules/'.$id.'/cancel', ['effective_from' => '2026-11-02'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.lecture-schedules.index'));
        $this->assertDatabaseHas('lecture_schedules', ['id' => $id, 'cancelled_from' => '2026-11-02']);
        $this->assertSame(0, DB::table('bookings')->where('status', 'approved')->count());
        $token = $this->upload([$this->row()]);
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('lecture_schedules', 1);
        $this->assertSame(0, DB::table('bookings')->where('status', 'approved')->count());
    }

    public function test_overlapping_rows_are_reported_and_prevent_the_entire_import(): void
    {
        $token = $this->upload([$this->row(), $this->row(['mata_kuliah_kelas' => 'Fisika - Kelas B', 'mulai' => '09:00', 'selesai' => '11:00'])]);
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSeeText('1 perlu diperbaiki')
            ->assertSeeText('jadwal pada baris 2');
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_existing_bookings_are_checked_again_after_preview(): void
    {
        $token = $this->upload([$this->row()]);
        $this->get('/admin/lecture-schedules/import')->assertSeeText('1 siap impor');
        $this->approvedBooking();
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSeeText('16-11-2026 08:00')->assertSeeText('Rapat disetujui');
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_a_schedule_created_after_preview_is_skipped_as_a_duplicate_on_confirmation(): void
    {
        $token = $this->upload([$this->row()]);
        $sources = session('lecture_schedule_import.rows');
        app(LectureScheduleCsvImport::class)->commit($sources, $this->admin->id);
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasNoErrors()
            ->assertSessionHas('master_success', 'Impor selesai: 0 pola jadwal dan 0 pertemuan dibuat. 1 baris duplikat dilewati.');
        $this->assertDatabaseCount('lecture_schedules', 1);
        $this->assertDatabaseCount('bookings', 4);
    }

    public function test_adjacent_times_and_pending_bookings_do_not_block_the_import(): void
    {
        $this->approvedBooking();
        DB::table('bookings')->insert([
            'room_id' => $this->room, 'user_id' => $this->admin->id,
            'purpose' => 'Pengajuan masih menunggu', 'participant_count' => 10,
            'starts_at' => '2026-11-09 10:00:00', 'ends_at' => '2026-11-09 12:00:00', 'status' => 'pending',
        ]);
        $token = $this->upload([$this->row(['mulai' => '10:00', 'selesai' => '12:00'])]);
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('lecture_schedules', 1);
        $this->assertDatabaseCount('bookings', 6);
    }

    public function test_capacity_and_room_status_are_checked_again_when_confirming(): void
    {
        foreach ([['capacity' => 30], ['capacity' => 50, 'is_active' => false]] as $change) {
            $token = $this->upload([$this->row()]);
            DB::table('rooms')->where('id', $this->room)->update($change);
            $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
            DB::table('rooms')->where('id', $this->room)->update(['capacity' => 50, 'is_active' => true]);
        }
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_failure_during_the_second_save_rolls_back_the_first_pattern_and_meetings(): void
    {
        $token = $this->upload([$this->row(), $this->row(['hari' => 'Selasa'])]);
        $manager = new class(app(BookingManagement::class)) extends LectureScheduleManagement
        {
            private int $saved = 0;

            public function save(array $data, int $actor, ?int $id = null): int
            {
                if (++$this->saved === 2) {
                    throw ValidationException::withMessages(['csv_file' => 'Penyimpanan pertemuan kedua gagal.']);
                }

                return parent::save($data, $actor, $id);
            }
        };
        $this->app->instance(LectureScheduleManagement::class, $manager);
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_semicolon_csv_with_bom_and_reordered_headers_is_supported(): void
    {
        $headers = array_reverse(LectureScheduleCsvImport::HEADERS);
        $file = $this->csv([$this->row(['mata_kuliah_kelas' => 'Kalkulus, kelas "A"; pagi'])], ';', $headers);
        $this->post('/admin/lecture-schedules/import/preview', ['csv_file' => $file])->assertSessionHasNoErrors();
        $token = session('lecture_schedule_import.token');
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('lecture_schedules', ['purpose' => 'Kalkulus, kelas "A"; pagi', 'user_id' => $this->lecturer->id]);
    }

    #[DataProvider('invalidRows')]
    public function test_invalid_rows_have_readable_errors_and_never_write_data(array $changes, string $message): void
    {
        $token = $this->upload([$this->row($changes)]);
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSeeText('1 perlu diperbaiki')->assertSeeText($message);
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseCount('lecture_schedules', 0);
        $this->assertDatabaseCount('bookings', 0);
    }

    public static function invalidRows(): array
    {
        return [
            'unknown room' => [['kode_ruangan' => 'TIDAK-ADA'], 'Kode ruangan tidak ditemukan'],
            'unknown lecturer' => [['nip_dosen' => '000000'], 'NIP dosen / administrator tidak ditemukan'],
            'too many participants' => [['peserta' => '51'], 'melebihi kapasitas'],
            'non integer participants' => [['peserta' => 'empat puluh'], 'bilangan bulat'],
            'outside opening hours' => [['mulai' => '07:00'], '07:30 WIB'],
            'weekend lecture' => [['hari' => 'Sabtu'], 'Hari kuliah'],
            'invalid date format' => [['awal_periode' => '02/11/2026'], 'YYYY-MM-DD'],
            'excessive period' => [['akhir_periode' => '2028-11-23'], '366 hari'],
            'no future meetings' => [['awal_periode' => '2026-11-03', 'akhir_periode' => '2026-11-03'], 'Tidak ada pertemuan mendatang'],
        ];
    }

    public function test_preview_escapes_uploaded_markup(): void
    {
        $this->upload([$this->row(['mata_kuliah_kelas' => '<script>alert(1)</script>'])]);
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_malformed_headers_empty_files_and_too_many_rows_are_rejected(): void
    {
        foreach ([
            UploadedFile::fake()->createWithContent('jadwal.csv', "ruang,dosen\n101,A\n"),
            UploadedFile::fake()->createWithContent('jadwal.csv', implode(',', LectureScheduleCsvImport::HEADERS)."\n"),
            $this->csv(array_fill(0, LectureScheduleCsvImport::MAX_ROWS + 1, $this->row())),
        ] as $file) {
            $this->post('/admin/lecture-schedules/import/preview', ['csv_file' => $file])->assertSessionHasErrors('csv_file');
        }
        $this->assertDatabaseCount('lecture_schedules', 0);
    }

    public function test_a_bad_replacement_upload_invalidates_the_previous_preview(): void
    {
        $token = $this->upload([$this->row()]);
        $this->post('/admin/lecture-schedules/import/preview', [])->assertSessionHasErrors('csv_file');
        $this->assertNull(session('lecture_schedule_import'));
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseCount('lecture_schedules', 0);
    }

    public function test_expired_tampered_and_other_admin_previews_cannot_be_confirmed(): void
    {
        $token = $this->upload([$this->row()]);
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => str_repeat('x', 40)])->assertSessionHasErrors('csv_file');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->actingAs($this->admin);
        $this->travel(31)->minutes();
        $this->post('/admin/lecture-schedules/import/confirm', ['token' => $token])->assertSessionHasErrors('csv_file');
        $this->get('/admin/lecture-schedules/import')->assertOk()->assertViewHas('preview', null);
        $this->assertDatabaseCount('lecture_schedules', 0);
    }

    public function test_only_admins_can_download_preview_and_confirm_imports(): void
    {
        foreach (['mahasiswa', 'dosen'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/admin/lecture-schedules/import')->assertForbidden();
            $this->get('/admin/lecture-schedules/import/template')->assertForbidden();
            $this->post('/admin/lecture-schedules/import/preview', ['csv_file' => $this->csv([$this->row()])])->assertForbidden();
            $this->post('/admin/lecture-schedules/import/confirm', ['token' => str_repeat('x', 40)])->assertForbidden();
        }
    }

    private function upload(array $rows): string
    {
        $this->post('/admin/lecture-schedules/import/preview', ['csv_file' => $this->csv($rows)])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.lecture-schedules.import.index'));

        return session('lecture_schedule_import.token');
    }

    private function row(array $changes = []): array
    {
        return array_replace([
            'semester' => 'Ganjil 2026/2027', 'kode_ruangan' => 'CSV-101', 'nip_dosen' => '001234567890',
            'mata_kuliah_kelas' => 'Kalkulus - Kelas A', 'hari' => 'Senin', 'mulai' => '08:00', 'selesai' => '10:00',
            'peserta' => '40', 'awal_periode' => '2026-11-02', 'akhir_periode' => '2026-11-23',
        ], $changes);
    }

    private function csv(array $rows, string $delimiter = ',', ?array $headers = null): UploadedFile
    {
        $headers ??= LectureScheduleCsvImport::HEADERS;
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers, $delimiter, '"', '');
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($key) => $row[$key], $headers), $delimiter, '"', '');
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return UploadedFile::fake()->createWithContent('jadwal.csv', $content);
    }

    private function approvedBooking(): void
    {
        DB::table('bookings')->insert([
            'room_id' => $this->room, 'user_id' => $this->admin->id,
            'purpose' => 'Rapat disetujui', 'participant_count' => 10,
            'starts_at' => '2026-11-16 08:00:00', 'ends_at' => '2026-11-16 10:00:00', 'status' => 'approved',
        ]);
    }
}
