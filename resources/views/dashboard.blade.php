<x-dashboard-layout>
    <div class="dash-page-heading">
        <div><p class="dash-eyebrow">RUANG KAMPUS, DALAM SATU TEMPAT</p><h1>Selamat datang, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}.</h1><p>Rencanakan kegiatan Anda. Temukan ruang yang tepat.</p></div>
        <span class="dash-today-label"><x-dashboard-icon name="calendar" />{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
    </div>

    <section class="dash-quick-actions" aria-label="Aksi cepat">
        <a href="{{ route('bookings.index') }}" class="dash-action-card">
            <div class="dash-action-top"><span class="dash-action-icon"><x-dashboard-icon name="list" /></span><span class="dash-action-number">{{ $activeBookings }}<small>aktif</small></span></div>
            <h2>Bookingan saya</h2><p>Pantau pengajuan dan rencana kegiatan Anda.</p>
            <div class="dash-action-bottom"><span>{{ $pendingBookings }} pengajuan menunggu</span><x-dashboard-icon name="arrow" /></div>
        </a>
        <a href="{{ route('rooms.index') }}" class="dash-action-card is-featured">
            <div class="dash-action-top"><span class="dash-action-icon"><x-dashboard-icon name="building" /></span><span class="dash-action-number">{{ $rooms->count() }}<small>ruang aktif</small></span></div>
            <h2>Booking ruang</h2><p>Temukan ruangan untuk ide dan kegiatan berikutnya.</p>
            <div class="dash-action-bottom"><span>Jelajahi ruangan</span><x-dashboard-icon name="arrow" /></div>
        </a>
        <a href="#calendar" class="dash-action-card">
            <div class="dash-action-top"><span class="dash-action-icon is-teal"><x-dashboard-icon name="calendar" /></span><span class="dash-action-kicker">RENCANAKAN LEBIH AWAL</span></div>
            <h2>Cek ketersediaan</h2><p>Lihat jadwal, lalu pilih tanggal yang sesuai.</p>
            <div class="dash-action-bottom"><span>Buka kalender ruangan</span><x-dashboard-icon name="arrow" /></div>
        </a>
    </section>

    <section id="calendar" class="dash-calendar-card" aria-labelledby="calendar-title">
        <div class="dash-section-heading">
            <div><p class="dash-eyebrow">ATUR WAKTU KEGIATAN ANDA</p><h2 id="calendar-title">Kalender ruangan</h2><p>Gambaran pemakaian ruang kampus, setiap hari.</p></div>
            <ul class="dash-legend" aria-label="Keterangan warna"><li><span class="dash-dot is-quiet"></span>Banyak ruang kosong</li><li><span class="dash-dot is-moderate"></span>Lumayan penuh</li><li><span class="dash-dot is-busy"></span>Sangat sibuk</li></ul>
        </div>

        <div class="dash-calendar-toolbar">
            <div class="dash-month-navigation">
                @if ($month->year > 2000 || $month->month > 1)
                    <a class="dash-icon-button" href="{{ route('dashboard', ['month' => $month->subMonth()->month, 'year' => $month->subMonth()->year]) }}#calendar" aria-label="Bulan sebelumnya"><x-dashboard-icon name="chevron-left" /></a>
                @else
                    <button class="dash-icon-button" disabled aria-label="Bulan sebelumnya"><x-dashboard-icon name="chevron-left" /></button>
                @endif
                <h3>{{ $month->translatedFormat('F Y') }}</h3>
                @if ($month->year < 2100 || $month->month < 12)
                    <a class="dash-icon-button" href="{{ route('dashboard', ['month' => $month->addMonth()->month, 'year' => $month->addMonth()->year]) }}#calendar" aria-label="Bulan berikutnya"><x-dashboard-icon name="chevron-right" /></a>
                @else
                    <button class="dash-icon-button" disabled aria-label="Bulan berikutnya"><x-dashboard-icon name="chevron-right" /></button>
                @endif
            </div>
            <form class="dash-filter" method="GET" action="{{ route('dashboard') }}#calendar">
                <label class="dash-sr-only" for="calendar-month">Bulan kalender</label>
                <select id="calendar-month" name="month">@foreach($months as $number => $label)<option value="{{ $number }}" @selected($month->month === $number)>{{ $label }}</option>@endforeach</select>
                <label class="dash-sr-only" for="calendar-year">Tahun kalender</label>
                <select id="calendar-year" name="year">@foreach(range(2000, 2100) as $year)<option value="{{ $year }}" @selected($month->year === $year)>{{ $year }}</option>@endforeach</select>
                <button type="submit" class="dash-button is-primary">Tampilkan</button>
                <a class="dash-button" href="{{ route('dashboard') }}#calendar">Hari ini</a>
            </form>
        </div>

        <p class="dash-calendar-mobile-hint">Geser kalender ke samping untuk melihat seluruh tanggal.</p>
        <div class="dash-calendar-scroll" role="region" aria-label="Kalender {{ $month->translatedFormat('F Y') }}" tabindex="0">
            <div class="dash-calendar-grid">
                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $weekday)<div class="dash-weekday">{{ $weekday }}</div>@endforeach
                @foreach ($days as $day)
                    @if ($day['in_month'])
                        <a href="{{ route('dashboard', ['month' => $month->month, 'year' => $month->year, 'date' => $day['date']->toDateString()]) }}#day-schedule" @class(['dash-day', 'is-'.$day['status'], 'is-selected' => $day['date']->isSameDay($selectedDate)]) @if($day['today']) aria-current="date" @endif aria-label="{{ $day['date']->locale('id')->translatedFormat('d F Y') }}, {{ $rooms->isEmpty() ? 'belum ada ruang aktif' : $day['occupied'].' dari '.$rooms->count().' ruang memiliki jadwal' }}">
                            <div class="dash-day-heading"><span @class(['dash-day-number', 'is-today' => $day['today']])>{{ $day['date']->day }}</span>@if($day['today'])<span class="dash-day-today">Hari ini</span>@endif</div>
                            @if ($rooms->isEmpty())
                                <span class="dash-day-empty">Belum ada ruang</span>
                            @else
                                <div class="dash-day-meter"><span></span><span></span><span></span><span></span><span></span></div>
                                <span class="dash-day-summary">{{ $day['occupied'] }} terjadwal</span><span class="dash-day-detail">{{ $day['free'] }} tanpa jadwal</span>
                            @endif
                        </a>
                    @else
                        <div class="dash-day is-outside" aria-hidden="true"><span class="dash-day-number">{{ $day['date']->day }}</span></div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="dash-calendar-footnote"><x-dashboard-icon name="info" /><div><p>Warna dihitung dari proporsi ruang aktif dengan booking <strong>disetujui</strong>, bukan durasi pemakaian.</p><details><summary>Bagaimana membaca kalender?</summary><p>Hijau: kurang dari 40% ruang terjadwal. Kuning: 40% sampai kurang dari 80%. Merah: 80% atau lebih. Satu ruang dihitung sekali per hari; booking menunggu, ditolak, dan dibatalkan tidak dihitung. Pilih tanggal untuk memeriksa jam pemakaian. Warna hijau tidak menjamin semua ruang kosong sepanjang hari.</p></details></div></div>
    </section>

    <section id="day-schedule" class="dash-day-section" aria-labelledby="selected-date-title">
        <div class="dash-section-heading"><div><p class="dash-eyebrow">TANGGAL YANG DIPILIH</p><h2 id="selected-date-title">{{ $selectedDate->translatedFormat('l, d F Y') }}</h2><p>{{ $rooms->count() - $selectedSchedule->count() }} dari {{ $rooms->count() }} ruang aktif belum memiliki jadwal disetujui.</p></div><a class="dash-text-link" href="{{ route('rooms.index', ['date' => $selectedDate->toDateString()]) }}">Lihat semua ruangan <x-dashboard-icon name="arrow" /></a></div>
        <div class="dash-room-grid">
            @forelse ($rooms->take(6) as $room)
                <article class="dash-room-card"><div class="dash-room-card-top"><span class="dash-room-code">{{ $room->code }}</span><span class="dash-room-capacity"><x-dashboard-icon name="users" />{{ $room->capacity }} kursi</span></div><h3>{{ $room->name }}</h3><p class="dash-room-location"><x-dashboard-icon name="pin" />{{ $room->location }}</p><x-room-slots :bookings="$selectedSchedule->get($room->id, collect())" :date="$selectedDate" /></article>
            @empty
                <div class="dash-empty"><x-dashboard-icon name="building" /><h3>Belum ada ruang aktif</h3><p>Kalender akan menampilkan pemakaian setelah data ruangan tersedia.</p></div>
            @endforelse
        </div>
    </section>
</x-dashboard-layout>
