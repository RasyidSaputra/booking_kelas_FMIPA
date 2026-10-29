<x-dashboard-layout title="Jadwal kuliah mingguan" :show-errors="false">
    @php
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
        $value = fn ($key, $default = '') => old($key, $editing?->{$key} ?? $default);
    @endphp
    <div class="master-page">
        <div class="dash-page-heading">
            <div><p class="dash-eyebrow">ADMINISTRASI PERKULIAHAN</p><h1>Jadwal kuliah mingguan</h1><p>Isi pola sekali untuk satu semester. Setiap pertemuan otomatis masuk kalender dan memblokir waktu peminjaman.</p></div>
            <div class="flex flex-wrap gap-2"><a class="dash-button is-primary" href="{{ route('admin.lecture-schedules.import.index') }}"><x-dashboard-icon name="plus" />Impor CSV</a><a class="dash-button" href="{{ route('admin.rooms.index', ['tab' => 'schedules']) }}"><x-dashboard-icon name="calendar" />Jadwal per tanggal</a></div>
        </div>
        @if(session('master_success'))<div class="master-success" role="status"><x-dashboard-icon name="check" />{{ session('master_success') }}</div>@endif
        @if($errors->any())<div class="dash-error" role="alert"><strong>Jadwal belum tersimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="master-panel mb-6" aria-labelledby="lecture-form-title">
            <div class="master-panel-toolbar"><div><h2 id="lecture-form-title">{{ $editing ? 'Ubah jadwal berikutnya' : 'Tambah jadwal mingguan' }}</h2><p>Satu pola untuk satu mata kuliah / kelas pada satu hari setiap minggu. Jam dalam WIB.</p></div>@if($editing)<a class="dash-button" href="{{ route('admin.lecture-schedules.index') }}">Batal edit</a>@endif</div>
            <form method="POST" action="{{ $editing ? route('admin.lecture-schedules.update', $editing->id) : route('admin.lecture-schedules.store') }}" class="master-modal-body">
                @csrf
                @if($editing) @method('PATCH') @endif
                <div class="master-form-grid">
                    <div class="master-field"><label for="lecture-semester">Semester</label><input id="lecture-semester" name="semester" value="{{ $value('semester') }}" maxlength="100" placeholder="Contoh: Ganjil 2026/2027" required></div>
                    <div class="master-field"><label for="lecture-purpose">Mata kuliah / kelas</label><input id="lecture-purpose" name="purpose" value="{{ $value('purpose') }}" maxlength="500" placeholder="Contoh: Kalkulus - Kelas A" required></div>
                    <div class="master-field"><label for="lecture-room">Ruangan</label><select id="lecture-room" name="room_id" required><option value="">Pilih ruangan aktif</option>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected((string) $value('room_id') === (string) $room->id) @disabled(! $room->is_active)>{{ $room->code }} — {{ $room->name }} ({{ $room->capacity }} orang){{ $room->is_active ? '' : ' · Nonaktif' }}</option>@endforeach</select></div>
                    <div class="master-field"><label for="lecture-user">Dosen / penanggung jawab</label><select id="lecture-user" name="user_id" required><option value="">Pilih dosen / administrator</option>@foreach($users as $person)<option value="{{ $person->id }}" @selected((string) $value('user_id') === (string) $person->id)>{{ $person->name }}</option>@endforeach</select></div>
                    <div class="master-field"><label for="lecture-day">Hari kuliah</label><select id="lecture-day" name="weekday" required>@foreach($days as $number => $day)<option value="{{ $number }}" @selected((int) $value('weekday', 1) === $number)>{{ $day }}</option>@endforeach</select></div>
                    <div class="master-field"><label for="lecture-count">Jumlah peserta</label><input id="lecture-count" name="participant_count" type="number" min="1" max="10000" value="{{ $value('participant_count') }}" required></div>
                    <div class="master-field"><label for="lecture-start-time">Jam mulai (WIB)</label><input id="lecture-start-time" name="start_time" type="time" min="07:30" max="17:59" value="{{ substr($value('start_time', '08:00'), 0, 5) }}" required></div>
                    <div class="master-field"><label for="lecture-end-time">Jam selesai (WIB)</label><input id="lecture-end-time" name="end_time" type="time" min="07:31" max="18:00" value="{{ substr($value('end_time', '10:00'), 0, 5) }}" required></div>
                    <div class="master-field"><label for="lecture-start-date">Awal periode semester</label><input id="lecture-start-date" name="starts_on" type="date" value="{{ $value('starts_on', today()->toDateString()) }}" required><small>Pertemuan yang sudah lewat tidak dibuat ulang.</small></div>
                    <div class="master-field"><label for="lecture-end-date">Akhir periode semester</label><input id="lecture-end-date" name="ends_on" type="date" min="{{ today()->toDateString() }}" value="{{ $value('ends_on') }}" required><small>Maksimal 366 hari dari awal periode.</small></div>
                    @if($editing)
                        <div class="master-field master-full"><label for="lecture-effective">Terapkan perubahan mulai tanggal</label><input id="lecture-effective" name="effective_from" type="date" min="{{ today()->toDateString() }}" value="{{ old('effective_from', today()->toDateString()) }}" required><small>Hanya pertemuan yang belum dimulai pada atau setelah tanggal ini yang diganti. Pertemuan yang diedit atau dibatalkan satu per satu tetap dipertahankan. Menyimpan pola akan mengaktifkan kembali rangkaian yang pernah dihentikan.</small></div>
                    @endif
                    <p class="master-form-note master-full">Semua pertemuan diperiksa sebelum disimpan. Jika ada satu bentrok dengan kuliah atau peminjaman disetujui, seluruh perubahan dibatalkan. Untuk hari libur atau kuliah pengganti, edit atau batalkan satu pertemuan melalui Jadwal per tanggal.</p>
                </div>
                <div class="mt-5 flex justify-end"><button class="dash-button is-primary" type="submit"><x-dashboard-icon name="check" />{{ $editing ? 'Simpan jadwal berikutnya' : 'Buat pertemuan otomatis' }}</button></div>
            </form>
        </section>

        <section class="master-panel" aria-labelledby="lecture-list-title">
            <div class="master-panel-toolbar"><div><h2 id="lecture-list-title">Pola jadwal per semester</h2><p>{{ $schedules->total() }} pola jadwal. Pertemuan selesai tetap tersedia sebagai riwayat.</p></div></div>
            @if($schedules->isEmpty())
                <div class="dash-empty"><x-dashboard-icon name="calendar" /><h2>Belum ada jadwal kuliah mingguan</h2><p>Tambahkan mata kuliah, hari, jam, dan periode semester melalui form di atas.</p></div>
            @else
                <div class="dash-table-scroll"><table class="dash-table master-table">
                    <thead><tr><th scope="col">Mata kuliah / kelas</th><th scope="col">Ruangan / dosen</th><th scope="col">Pola mingguan</th><th scope="col">Periode</th><th scope="col">Tindakan</th></tr></thead>
                    <tbody>@foreach($schedules as $schedule)
                        <tr>
                            <td><strong>{{ $schedule->purpose }}</strong><small>{{ $schedule->semester }} · {{ $schedule->participant_count }} peserta</small></td>
                            <td><strong>{{ $schedule->room_name }}</strong><small>{{ $schedule->room_code }} · {{ $schedule->user_name }}</small></td>
                            <td><strong>Setiap {{ $days[$schedule->weekday] }}</strong><small>{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} WIB</small></td>
                            <td><strong>{{ \Carbon\CarbonImmutable::parse($schedule->starts_on)->format('d-m-Y') }}</strong><small>sampai {{ \Carbon\CarbonImmutable::parse($schedule->ends_on)->format('d-m-Y') }}</small>@if($schedule->cancelled_from)<small>Dihentikan mulai {{ \Carbon\CarbonImmutable::parse($schedule->cancelled_from)->format('d-m-Y') }}</small>@elseif($schedule->ends_on < today()->toDateString())<small>Periode selesai</small>@endif</td>
                            <td>
                                <a class="dash-button" href="{{ route('admin.lecture-schedules.index', ['edit' => $schedule->id]) }}#lecture-form-title">Ubah jadwal berikutnya</a>
                                @if(! $schedule->cancelled_from && $schedule->ends_on >= today()->toDateString())
                                    <form method="POST" action="{{ route('admin.lecture-schedules.cancel', $schedule->id) }}" class="master-field mt-3">
                                        @csrf @method('PATCH')
                                        <label for="stop-{{ $schedule->id }}">Hentikan pertemuan mulai</label>
                                        <input id="stop-{{ $schedule->id }}" type="date" name="effective_from" value="{{ today()->toDateString() }}" min="{{ today()->toDateString() }}" required>
                                        <button type="submit" class="dash-button mt-2">Hentikan rangkaian</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach</tbody>
                </table></div>
            @endif
        </section>
        <div class="dash-pagination">{{ $schedules->links() }}</div>
    </div>
</x-dashboard-layout>
