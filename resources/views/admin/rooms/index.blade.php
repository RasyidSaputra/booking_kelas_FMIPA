<x-dashboard-layout title="Master data kelas" :show-errors="false">
    @php
        $tabs = [
            'rooms' => ['Ruangan', 'building'],
            'facilities' => ['Fasilitas', 'grid'],
            'bookings' => ['Pengajuan', 'list'],
            'schedules' => ['Jadwal', 'calendar'],
            'users' => ['Pengguna', 'users'],
        ];
        $create = ['rooms' => ['room', 'Tambah ruangan'], 'facilities' => ['facility', 'Tambah fasilitas'], 'schedules' => ['schedule', 'Buat jadwal'], 'users' => ['user', 'Tambah pengguna']];
        $roles = ['admin' => 'Administrator', 'dosen' => 'Dosen', 'mahasiswa' => 'Mahasiswa'];
    @endphp
    <div class="master-page" x-data="adminMaster(@js([
        'rooms' => route('admin.rooms.store'),
        'facilities' => route('admin.facilities.store'),
        'users' => route('admin.users.store'),
        'schedules' => route('admin.schedules.store'),
        'bookings' => url('/admin/bookings'),
    ]))">
        <div class="dash-page-heading">
            <div><p class="dash-eyebrow">PUSAT ADMINISTRASI FMIPA</p><h1>Master data kelas</h1><p>Ruangan, pengajuan, jadwal, dan pengguna. Semua dalam satu tempat.</p></div>
            @if(isset($create[$tab]))
                <button class="dash-button is-primary master-add-button" type="button" x-on:click="open('{{ $create[$tab][0] }}')"><x-dashboard-icon name="plus" />{{ $create[$tab][1] }}</button>
            @else
                <span class="dash-access-badge">Akses administrator</span>
            @endif
        </div>

        @if(session('master_success'))<div class="master-success" role="status"><x-dashboard-icon name="check" />{{ session('master_success') }}</div>@endif
        @if($errors->any())<div class="dash-error" role="alert"><strong>Data belum tersimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="master-stats" aria-label="Ringkasan administrasi">
            <a href="{{ route('admin.rooms.index', ['tab' => 'rooms']) }}"><span class="master-stat-icon"><x-dashboard-icon name="building" /></span><div><small>Total ruangan</small><strong>{{ $stats['rooms'] }}</strong></div><x-dashboard-icon name="chevron-right" /></a>
            <a href="{{ route('admin.rooms.index', ['tab' => 'bookings']) }}"><span class="master-stat-icon is-amber"><x-dashboard-icon name="list" /></span><div><small>Menunggu persetujuan</small><strong>{{ $stats['pending'] }}</strong></div><x-dashboard-icon name="chevron-right" /></a>
            <a href="{{ route('admin.rooms.index', ['tab' => 'schedules']) }}"><span class="master-stat-icon is-teal"><x-dashboard-icon name="calendar" /></span><div><small>Jadwal aktif</small><strong>{{ $stats['schedules'] }}</strong></div><x-dashboard-icon name="chevron-right" /></a>
            <a href="{{ route('admin.rooms.index', ['tab' => 'users']) }}"><span class="master-stat-icon"><x-dashboard-icon name="users" /></span><div><small>Pengguna terdaftar</small><strong>{{ $stats['users'] }}</strong></div><x-dashboard-icon name="chevron-right" /></a>
        </section>

        <section class="master-panel" aria-label="Pengelolaan master data">
            <nav class="master-tabs" aria-label="Bagian master data">
                @foreach($tabs as $key => [$label, $icon])
                    <a href="{{ route('admin.rooms.index', ['tab' => $key]) }}" @class(['master-tab', 'is-active' => $tab === $key]) @if($tab === $key) aria-current="page" @endif><x-dashboard-icon :name="$icon" />{{ $label }}@if($key === 'bookings' && $stats['pending'])<span>{{ $stats['pending'] }}</span>@endif</a>
                @endforeach
            </nav>
            <div class="master-panel-toolbar">
                <div><h2>{{ $tabs[$tab][0] }}</h2><p>{{ $items->total() }} data ditemukan @if($tab === 'schedules') · {{ $date->translatedFormat('d F Y') }}@endif</p></div>
                <form class="dash-filter master-filter" method="GET" action="{{ route('admin.rooms.index') }}">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <label class="dash-sr-only" for="master-search">Cari {{ strtolower($tabs[$tab][0]) }}</label>
                    <input id="master-search" name="q" type="search" placeholder="Cari {{ strtolower($tabs[$tab][0]) }}…" value="{{ $search }}" maxlength="100">
                    @if(in_array($tab, ['rooms', 'bookings']))
                        <label class="dash-sr-only" for="master-status">Filter status</label>
                        <select id="master-status" name="status">
                            @foreach(($tab === 'rooms' ? ['all' => 'Semua status', 'active' => 'Aktif', 'inactive' => 'Nonaktif'] : ['all' => 'Semua status', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan']) as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if($tab === 'schedules')<label class="dash-sr-only" for="master-date">Tanggal jadwal</label><input id="master-date" type="date" name="date" value="{{ $date->toDateString() }}" required>@endif
                    <button type="submit" class="dash-button">Tampilkan</button>
                </form>
            </div>

            @if($items->isEmpty())
                <div class="dash-empty master-empty"><x-dashboard-icon :name="$tabs[$tab][1]" /><h2>Belum ada data {{ strtolower($tabs[$tab][0]) }}</h2><p>{{ $search !== '' ? 'Coba kata pencarian atau filter lain.' : ($tab === 'bookings' ? 'Pengajuan peminjaman akan tampil di sini untuk diperiksa.' : 'Gunakan tombol tambah untuk melengkapi data.') }}</p></div>
            @else
                <div class="dash-table-scroll">
                    <table class="dash-table master-table">
                        <thead><tr>
                            @if($tab === 'rooms')<th scope="col">Ruangan</th><th scope="col">Lokasi</th><th scope="col">Kapasitas</th><th scope="col">Status</th>
                            @elseif($tab === 'facilities')<th scope="col">Fasilitas</th><th scope="col">Keterangan</th>
                            @elseif($tab === 'users')<th scope="col">Pengguna</th><th scope="col">NIM / NIP</th><th scope="col">Email</th><th scope="col">Role</th>
                            @else<th scope="col">Ruangan / kegiatan</th><th scope="col">Penanggung jawab</th><th scope="col">Waktu pemakaian</th><th scope="col">Status</th>
                            @endif
                            <th scope="col" class="master-actions-heading">Tindakan</th>
                        </tr></thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    @if($tab === 'rooms')
                                        <td><div class="master-room-name">@if($item->photo_url)<img src="{{ $item->photo_url }}" alt="" loading="lazy">@else<span class="master-room-placeholder"><x-dashboard-icon name="building" /></span>@endif<div><strong>{{ $item->name }}</strong><small>{{ $item->code }}</small></div></div></td>
                                        <td>{{ $item->location }}</td><td>{{ $item->capacity }} orang</td><td><span @class(['dash-status', 'is-approved' => $item->is_active])>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                        <td><div class="master-row-actions"><button type="button" x-on:click="open('room', @js($item))">Edit</button><button type="button" class="is-danger" x-on:click="open('delete-room', @js($item))">Hapus</button></div></td>
                                    @elseif($tab === 'facilities')
                                        <td><strong>{{ $item->name }}</strong></td><td>Fasilitas yang dapat dipilih pada informasi ruangan.</td>
                                        <td><div class="master-row-actions"><button type="button" x-on:click="open('facility', @js($item))">Edit</button><button type="button" class="is-danger" x-on:click="open('delete-facility', @js($item))">Hapus</button></div></td>
                                    @elseif($tab === 'users')
                                        <td><strong>{{ $item->name }} @if($item->id === auth()->id())<span class="master-you">Anda</span>@endif</strong></td><td>{{ $item->identity_number ?: 'Belum diisi' }}</td><td>{{ $item->email }}</td><td><span class="dash-status {{ $item->role === 'admin' ? 'is-approved' : '' }}">{{ $roles[$item->role] ?? $item->role }}</span></td>
                                        <td><div class="master-row-actions"><button type="button" x-on:click="open('user', @js($item))">Edit</button><button type="button" class="is-danger" @disabled($item->id === auth()->id()) x-on:click="open('delete-user', @js($item))">Hapus</button></div></td>
                                    @else
                                        <td><strong>{{ $item->room_name }}</strong><small>{{ $item->room_code }} · {{ $item->participant_count }} peserta</small><p class="master-purpose">{{ $item->purpose }}</p>@if($item->rejection_reason)<small>Alasan: {{ $item->rejection_reason }}</small>@endif</td>
                                        <td><strong>{{ $item->user_name }}</strong><small>{{ $item->identity_number }}</small></td>
                                        <td><strong>{{ \Carbon\CarbonImmutable::parse($item->starts_at)->locale('id')->translatedFormat('d M Y, H:i') }}</strong><small>sampai {{ \Carbon\CarbonImmutable::parse($item->ends_at)->locale('id')->translatedFormat('d M Y, H:i') }}</small></td>
                                        <td><x-booking-status :status="$item->status" /></td>
                                        <td><div class="master-row-actions">
                                            @if($tab === 'bookings' && $item->status === 'pending')
                                                <button type="button" class="is-approve" x-on:click="open('approve', @js($item))">Setujui</button><button type="button" class="is-danger" x-on:click="open('reject', @js($item))">Tolak</button>
                                            @elseif($tab === 'schedules')
                                                <button type="button" x-on:click="open('schedule', @js($item))">Edit</button><button type="button" class="is-danger" x-on:click="open('cancel', @js($item))">Batalkan</button>
                                            @else<span class="master-readonly">Sudah diproses</span>@endif
                                        </div></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            @if($tab === 'schedules')<p class="master-panel-note"><x-dashboard-icon name="info" />Jadwal adalah peminjaman disetujui. Pembatalan tidak menghapus riwayat.</p>@endif
        </section>
        <div class="dash-pagination">{{ $items->links() }}</div>
        @include('admin.rooms.partials.modal')
    </div>
</x-dashboard-layout>
