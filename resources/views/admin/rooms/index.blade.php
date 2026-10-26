<x-dashboard-layout title="Master data kelas">
    <div class="dash-page-heading"><div><p class="dash-eyebrow">ADMINISTRASI RUANGAN</p><h1>Master data kelas</h1><p>Daftar seluruh ruangan, termasuk yang sedang dinonaktifkan.</p></div><span class="dash-access-badge">Akses administrator</span></div>
    <section class="dash-directory-filter"><form class="dash-filter" method="GET" action="{{ route('admin.rooms.index') }}"><div><label for="master-search">Cari data kelas</label><input id="master-search" type="search" name="q" value="{{ $search }}" placeholder="Nama, kode, atau lokasi…" maxlength="100"></div><button type="submit" class="dash-button is-primary">Cari</button><a href="{{ route('admin.rooms.index') }}" class="dash-text-link">Reset filter</a></form></section>
    <section class="dash-table-card" aria-label="Master data kelas">
        @if ($rooms->isEmpty())
            <div class="dash-empty"><x-dashboard-icon name="building" /><h2>{{ $search !== '' ? 'Kelas tidak ditemukan' : 'Belum ada data kelas' }}</h2><p>{{ $search !== '' ? 'Gunakan kata pencarian lain.' : 'Data kelas akan muncul di sini setelah ruangan ditambahkan.' }}</p></div>
        @else
            <div class="dash-table-scroll"><table class="dash-table"><thead><tr><th scope="col">Kode</th><th scope="col">Nama kelas</th><th scope="col">Lokasi</th><th scope="col">Kapasitas</th><th scope="col">Status</th></tr></thead><tbody>@foreach($rooms as $room)<tr><td><span class="dash-room-code">{{ $room->code }}</span></td><td><strong>{{ $room->name }}</strong></td><td>{{ $room->location }}</td><td>{{ $room->capacity }} kursi</td><td><span @class(['dash-status', 'is-approved' => $room->is_active])>{{ $room->is_active ? 'Aktif' : 'Nonaktif' }}</span></td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
    <div class="dash-pagination">{{ $rooms->links() }}</div>
</x-dashboard-layout>
