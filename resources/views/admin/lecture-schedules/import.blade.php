<x-dashboard-layout title="Impor jadwal kuliah" :show-errors="false">
    <div class="master-page">
        <div class="dash-page-heading">
            <div><p class="dash-eyebrow">ADMINISTRASI PERKULIAHAN</p><h1>Impor jadwal kuliah</h1><p>Unggah banyak pola mingguan sekaligus. Periksa pratinjau sebelum membuat seluruh pertemuan semester.</p></div>
            <a class="dash-button" href="{{ route('admin.lecture-schedules.index') }}"><x-dashboard-icon name="calendar" />Kembali ke jadwal</a>
        </div>
        @if($errors->any())<div class="dash-error" role="alert"><strong>Impor belum disimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="master-panel mb-6" aria-labelledby="import-upload-title">
            <div class="master-panel-toolbar"><div><h2 id="import-upload-title">1. Siapkan file CSV</h2><p>Satu baris untuk satu mata kuliah / kelas pada satu hari setiap minggu.</p></div><a class="dash-button" href="{{ route('admin.lecture-schedules.import.template') }}"><x-dashboard-icon name="arrow" />Unduh template CSV</a></div>
            <div class="master-modal-body">
                <p class="master-form-note mb-5">Isi template melalui Excel atau aplikasi spreadsheet, lalu pilih <strong>Save As → CSV UTF-8 (.csv)</strong>. Pemisah koma atau titik koma didukung. Maksimal {{ \App\Services\LectureScheduleCsvImport::MAX_ROWS }} baris jadwal dan 1 MB per file. Unggah file baru untuk mengganti pratinjau sebelumnya.</p>
                <details class="mb-5 text-sm">
                    <summary class="cursor-pointer font-semibold">Panduan kolom dan pencocokan data</summary>
                    <div class="dash-table-scroll mt-3"><table class="dash-table master-table"><thead><tr><th scope="col">Kolom</th><th scope="col">Isi yang diperlukan</th></tr></thead><tbody>
                        <tr><td>semester</td><td>Contoh: Ganjil 2026/2027.</td></tr>
                        <tr><td>kode_ruangan</td><td>Kode ruangan yang sudah terdaftar dan aktif, misalnya FMIPA-101.</td></tr>
                        <tr><td>nip_dosen</td><td>NIP akun dosen atau administrator yang sudah terdaftar. Atur kolom Excel sebagai <strong>Text</strong> agar angka nol di depan tetap tersimpan. Ganti NIP contoh pada template.</td></tr>
                        <tr><td>mata_kuliah_kelas</td><td>Nama mata kuliah dan kelas, misalnya Kalkulus - Kelas A.</td></tr>
                        <tr><td>hari</td><td>Senin, Selasa, Rabu, Kamis, atau Jumat. Angka 1–5 juga diterima.</td></tr>
                        <tr><td>mulai / selesai</td><td>Jam WIB dengan format HH:MM, misalnya 08:00 dan 10:00. Jam operasional 07:30–18:00.</td></tr>
                        <tr><td>peserta</td><td>Bilangan bulat positif yang tidak melebihi kapasitas ruangan.</td></tr>
                        <tr><td>awal_periode / akhir_periode</td><td>Tanggal YYYY-MM-DD. Periode maksimal 366 hari; pertemuan yang sudah lewat tidak dibuat ulang.</td></tr>
                    </tbody></table></div>
                    <p class="master-form-note mt-3">Periksa kode ruangan dan NIP melalui <a class="underline" href="{{ route('admin.rooms.index') }}">master data kelas</a>. Urutan kolom boleh diubah selama nama semua kolom tetap sesuai template.</p>
                </details>
                <form method="POST" action="{{ route('admin.lecture-schedules.import.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="master-field"><label for="import-csv">File jadwal kuliah (.csv)</label><input id="import-csv" name="csv_file" type="file" accept=".csv,text/csv" required aria-describedby="import-csv-help"><small id="import-csv-help">Unggah untuk memeriksa isi file. Jadwal disimpan setelah Anda menekan Simpan hasil impor.</small></div>
                    <div class="mt-5 flex justify-end"><button class="dash-button is-primary" type="submit"><x-dashboard-icon name="search" />Periksa dan tampilkan pratinjau</button></div>
                </form>
            </div>
        </section>

        @if($preview)
            @php
                $labels = ['ready' => 'Siap impor', 'duplicate' => 'Duplikat · Dilewati', 'invalid' => 'Perlu diperbaiki'];
                $meetingCount = collect($preview['rows'])->where('status', 'ready')->sum('meetings');
            @endphp
            <section class="master-panel" aria-labelledby="import-preview-title">
                <div class="master-panel-toolbar"><div><h2 id="import-preview-title">2. Periksa pratinjau</h2><p>{{ $preview['ready'] }} siap impor · {{ $preview['duplicate'] }} duplikat · {{ $preview['invalid'] }} perlu diperbaiki · {{ $meetingCount }} pertemuan baru</p></div></div>
                @if($preview['invalid'] > 0)
                    <p class="master-form-note">Perbaiki seluruh baris bermasalah di file, lalu unggah ulang. Penyimpanan dilakukan sekaligus setelah semua baris valid; duplikat akan dilewati.</p>
                @elseif($preview['ready'] === 0)
                    <p class="master-form-note">Semua baris sudah terdaftar. Tidak ada jadwal baru untuk disimpan. Jadwal yang tersimpan tetap dipertahankan.</p>
                @else
                    <p class="master-form-note">Pratinjau berlaku selama 30 menit sejak unggah. Sistem memeriksa ulang bentrok, kapasitas, dan duplikat saat penyimpanan. Jika ada kesalahan baru, seluruh impor dibatalkan.</p>
                @endif
                <div class="dash-table-scroll"><table class="dash-table master-table">
                    <thead><tr><th scope="col">Baris</th><th scope="col">Mata kuliah / semester</th><th scope="col">Ruangan / NIP</th><th scope="col">Jadwal / periode</th><th scope="col">Hasil pemeriksaan</th></tr></thead>
                    <tbody>@foreach($preview['rows'] as $row)
                        <tr>
                            <td>{{ $row['line'] }}</td>
                            <td><strong>{{ $row['values']['mata_kuliah_kelas'] }}</strong><small>{{ $row['values']['semester'] }} · {{ $row['values']['peserta'] }} peserta</small></td>
                            <td><strong>{{ $row['values']['kode_ruangan'] }}</strong><small>{{ $row['values']['nip_dosen'] }}</small></td>
                            <td><strong>{{ $row['values']['hari'] }}, {{ $row['values']['mulai'] }}–{{ $row['values']['selesai'] }} WIB</strong><small>{{ $row['values']['awal_periode'] }} sampai {{ $row['values']['akhir_periode'] }}</small>@if($row['status'] === 'ready')<small>{{ $row['meetings'] }} pertemuan mendatang</small>@endif</td>
                            <td><span @class(['dash-status', 'is-approved' => $row['status'] === 'ready', 'is-rejected' => $row['status'] === 'invalid'])>{{ $labels[$row['status']] }}</span>@foreach($row['errors'] as $message)<p class="{{ $row['status'] === 'invalid' ? 'master-field-error' : 'master-purpose' }}">{{ $message }}</p>@endforeach</td>
                        </tr>
                    @endforeach</tbody>
                </table></div>
                <form method="POST" action="{{ route('admin.lecture-schedules.import.confirm') }}" class="master-modal-body">
                    @csrf
                    <input type="hidden" name="token" value="{{ $pending['token'] }}">
                    <div class="flex justify-end"><button class="dash-button is-primary" type="submit" @disabled($preview['invalid'] > 0 || $preview['ready'] === 0)><x-dashboard-icon name="check" />Simpan hasil impor ({{ $preview['ready'] }} pola)</button></div>
                </form>
            </section>
        @endif
    </div>
</x-dashboard-layout>
