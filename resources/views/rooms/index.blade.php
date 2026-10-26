<x-dashboard-layout title="Booking ruang">
    <div class="dash-page-heading"><div><p class="dash-eyebrow">TEMUKAN RUANG ANDA</p><h1>Booking ruang</h1><p>Jelajahi ruang aktif dan periksa jadwal untuk rencana kegiatan Anda.</p></div><a class="dash-button" href="{{ route('dashboard') }}#calendar"><x-dashboard-icon name="calendar" />Buka kalender</a></div>
    <section class="dash-directory-filter">
        <form class="dash-filter" action="{{ route('rooms.index') }}" method="GET">
            <div><label for="room-search">Nama, kode, atau lokasi</label><input id="room-search" name="q" type="search" value="{{ $search }}" placeholder="Cari ruangan…" maxlength="100"></div>
            <div><label for="room-date">Tanggal pemakaian</label><input id="room-date" name="date" type="date" value="{{ $date->toDateString() }}" required></div>
            <button class="dash-button is-primary" type="submit"><x-dashboard-icon name="search" />Cari ruangan</button>
            <a href="{{ route('rooms.index') }}" class="dash-text-link">Reset filter</a>
        </form>
    </section>
    <div class="dash-list-heading"><h2>{{ $rooms->total() }} ruang ditemukan</h2><p>Jadwal disetujui · {{ $date->translatedFormat('d F Y') }}</p></div>
    <div class="dash-room-grid">
        @forelse ($rooms as $room)
            <article class="dash-room-card">@if($room->photo_path)<img class="dash-room-photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($room->photo_path) }}" alt="{{ $room->name }}" loading="lazy">@endif<div class="dash-room-card-top"><span class="dash-room-code">{{ $room->code }}</span><span class="dash-room-capacity"><x-dashboard-icon name="users" />{{ $room->capacity }} kursi</span></div><h3>{{ $room->name }}</h3><p class="dash-room-location"><x-dashboard-icon name="pin" />{{ $room->location }}</p>@if($room->description)<p class="dash-room-description">{{ $room->description }}</p>@endif<x-room-slots :bookings="$schedule->get($room->id, collect())" :date="$date" /></article>
        @empty
            <div class="dash-empty"><x-dashboard-icon name="search" /><h3>{{ $search !== '' ? 'Ruangan tidak ditemukan' : 'Belum ada ruang aktif' }}</h3><p>{{ $search !== '' ? 'Coba nama, kode, atau lokasi lain.' : 'Data ruangan aktif akan tampil di sini setelah tersedia.' }}</p></div>
        @endforelse
    </div>
    <div class="dash-pagination">{{ $rooms->links() }}</div>
</x-dashboard-layout>
