<x-dashboard-layout title="Booking ruang">
    <div class="dash-page-heading"><div><p class="dash-eyebrow">TEMUKAN RUANG ANDA</p><h1>Booking ruang</h1><p>Jelajahi ruang aktif dan periksa jadwal untuk rencana kegiatan Anda.</p></div><a class="dash-button" href="{{ route('dashboard') }}#calendar"><x-dashboard-icon name="calendar" />Buka kalender</a></div>
    <section class="dash-directory-filter space-y-4">
        <form class="dash-filter flex flex-wrap items-end gap-3" action="{{ route('rooms.index') }}" method="GET">
            <div class="flex-1 min-w-[200px]">
                <label for="room-search">Nama, kode, atau lokasi</label>
                <input id="room-search" name="q" type="search" value="{{ $search }}" placeholder="Cari ruangan…" maxlength="100">
            </div>
            <div class="min-w-[150px]">
                <label for="room-date">Tanggal pemakaian</label>
                <input id="room-date" name="date" type="date" value="{{ $date->toDateString() }}" required>
            </div>
            <div class="min-w-[140px]">
                <label for="room-capacity" class="block text-xs font-semibold text-gray-600 mb-1">Kapasitas minimal</label>
                <select id="room-capacity" name="capacity" class="w-full h-[38px] rounded-lg border-gray-300 text-xs text-gray-700 shadow-2xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="0" @selected($capacity === 0)>Semua Kapasitas</option>
                    <option value="40" @selected($capacity === 40)>&ge; 40 Kursi</option>
                    <option value="50" @selected($capacity === 50)>&ge; 50 Kursi</option>
                    <option value="60" @selected($capacity === 60)>&ge; 60 Kursi</option>
                </select>
            </div>
            <div class="min-w-[150px]">
                <label for="room-availability" class="block text-xs font-semibold text-gray-600 mb-1">Ketersediaan</label>
                <select id="room-availability" name="availability" class="w-full h-[38px] rounded-lg border-gray-300 text-xs text-gray-700 shadow-2xs focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="all" @selected($availability === 'all')>Semua Ruangan</option>
                    <option value="free" @selected($availability === 'free')>🟢 Bebas Sepanjang Hari</option>
                </select>
            </div>

            <input type="hidden" name="floor" value="{{ $floor }}">

            <div class="flex items-center gap-2 self-end">
                <button class="dash-button is-primary" type="submit"><x-dashboard-icon name="search" />Cari</button>
                <a href="{{ route('rooms.index') }}" class="dash-text-link">Reset</a>
            </div>
        </form>

        {{-- Quick Pills untuk Lantai --}}
        <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-semibold text-gray-500">Pilih Lantai:</span>
                @foreach([
                    'all' => 'Semua Lantai',
                    '1' => 'Lantai 1',
                    '2' => 'Lantai 2',
                    '3' => 'Lantai 3',
                ] as $fKey => $fLabel)
                    <a href="{{ route('rooms.index', array_merge(request()->query(), ['floor' => $fKey, 'page' => 1])) }}"
                       class="px-3 py-1 rounded-full text-xs font-medium transition-colors border {{ $floor === $fKey ? 'bg-emerald-700 text-white border-emerald-700 shadow-2xs font-semibold' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                        {{ $fLabel }}
                    </a>
                @endforeach
            </div>

            @if($floor !== 'all' || $capacity > 0 || $availability !== 'all' || $search !== '')
                <div class="text-[11px] text-gray-400">
                    Filter aktif:
                    @if($floor !== 'all') <span class="text-gray-700 font-medium">Lt. {{ $floor }}</span> @endif
                    @if($capacity > 0) <span class="text-gray-700 font-medium">· &ge;{{ $capacity }} kursi</span> @endif
                    @if($availability === 'free') <span class="text-emerald-700 font-medium">· Bebas Seharian</span> @endif
                    @if($search !== '') <span class="text-gray-700 font-medium">· "{{ $search }}"</span> @endif
                </div>
            @endif
        </div>
    </section>

    <div class="dash-list-heading flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2>{{ $rooms->total() }} ruang ditemukan</h2>
            <p>Jadwal disetujui · {{ $date->translatedFormat('d F Y') }}</p>
        </div>
        @if($isToday)
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span><strong>{{ $currentlyAvailableCount }}</strong> Tersedia Saat Ini</span>
                </span>
                @if($currentlyOccupiedCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-800 border border-rose-200 font-medium">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <span><strong>{{ $currentlyOccupiedCount }}</strong> Sedang Digunakan</span>
                    </span>
                @endif
            </div>
        @endif
    </div>
    <div class="dash-room-grid">
        @forelse ($rooms as $room)
            @php
                $roomBookings = $schedule->get($room->id, collect());
                $now = now();
                $isToday = $date->isToday();
                $currentBooking = $isToday ? $roomBookings->first(fn($b) => $b->starts_at <= $now->toDateTimeString() && $b->ends_at >= $now->toDateTimeString()) : null;
            @endphp
            <article class="dash-room-card flex flex-col justify-between">
                <div>
                    @if($room->photo_path)
                        <img class="dash-room-photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($room->photo_path) }}" alt="{{ $room->name }}" loading="lazy">
                    @endif
                    <div class="dash-room-card-top flex flex-wrap items-center justify-between gap-2">
                        <span class="dash-room-code">{{ $room->code }}</span>

                        {{-- Indikator Status Real-time Langsung di Card --}}
                        @if($isToday)
                            @if($currentBooking)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="Digunakan untuk: {{ $currentBooking->purpose }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>Digunakan s/d {{ \Carbon\CarbonImmutable::parse($currentBooking->ends_at)->format('H:i') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Tersedia Saat Ini</span>
                                </span>
                            @endif
                        @else
                            @if($roomBookings->isEmpty())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Bebas Seharian</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>{{ $roomBookings->count() }} Sesi Terjadwal</span>
                                </span>
                            @endif
                        @endif

                        <span class="dash-room-capacity ml-auto"><x-dashboard-icon name="users" />{{ $room->capacity }} kursi</span>
                    </div>

                    <h3 class="mt-3"><a href="{{ route('rooms.show', $room->id) }}" class="hover:underline hover:text-emerald-700 transition-colors">{{ $room->name }}</a></h3>
                    <p class="dash-room-location"><x-dashboard-icon name="pin" />{{ $room->location }}</p>
                    @if($room->description)
                        <p class="dash-room-description">{{ $room->description }}</p>
                    @endif
                </div>

                <div>
                    {{-- Mini Visual Timeline Bar (07:30 - 18:00) --}}
                    <div class="mt-2 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between text-[10px] text-gray-400 font-mono mb-1.5">
                            <span>07:30</span>
                            <span class="text-[9px] uppercase tracking-wider font-sans font-medium text-gray-400">Jadwal 07:30 – 18:00</span>
                            <span>18:00</span>
                        </div>
                        
                        {{-- Timeline Track --}}
                        <div class="relative w-full h-2.5 bg-emerald-50 rounded-full overflow-hidden border border-emerald-200/60 shadow-2xs">
                            @foreach($roomBookings as $b)
                                @php
                                    $bStart = \Carbon\CarbonImmutable::parse($b->starts_at);
                                    $bEnd = \Carbon\CarbonImmutable::parse($b->ends_at);
                                    
                                    // 07:30 = 450 menit, 18:00 = 1080 menit (total 630 menit)
                                    $startMin = max(450, $bStart->hour * 60 + $bStart->minute);
                                    $endMin = min(1080, $bEnd->hour * 60 + $bEnd->minute);
                                    
                                    $left = max(0, min(100, (($startMin - 450) / 630) * 100));
                                    $width = max(2, min(100 - $left, (($endMin - $startMin) / 630) * 100));
                                @endphp
                                <div class="absolute top-0 bottom-0 bg-rose-400 hover:bg-rose-500 rounded-xs transition-colors cursor-pointer"
                                     style="left: {{ $left }}%; width: {{ $width }}%;"
                                     title="{{ $bStart->format('H:i') }} - {{ $bEnd->format('H:i') }}: {{ $b->purpose }}">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Daftar Slot Teks --}}
                    <x-room-slots :bookings="$roomBookings" :date="$date" />

                    {{-- Tombol Aksi Cepat: Pinjam Ruangan --}}
                    <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-gray-400 font-medium">Bebas bentrok?</span>
                        <a href="{{ route('rooms.show', $room->id) }}?book=1&date={{ $date->toDateString() }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 transition-colors shadow-2xs">
                            <x-dashboard-icon name="calendar" class="w-3.5 h-3.5" />
                            <span>Pinjam Ruangan</span>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="dash-empty col-span-full text-center py-12 bg-white rounded-xl border border-gray-200">
                <x-dashboard-icon name="search" />
                <h3 class="mt-2">{{ $search !== '' || $floor !== 'all' || $capacity > 0 || $availability !== 'all' ? 'Ruangan tidak ditemukan' : 'Belum ada ruang aktif' }}</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    {{ $search !== '' || $floor !== 'all' || $capacity > 0 || $availability !== 'all' ? 'Tidak ada ruangan yang sesuai dengan filter yang dipilih. Coba sesuaikan pilihan filter Anda.' : 'Data ruangan aktif akan tampil di sini setelah tersedia.' }}
                </p>
                @if($search !== '' || $floor !== 'all' || $capacity > 0 || $availability !== 'all')
                    <div class="mt-4">
                        <a href="{{ route('rooms.index') }}" class="dash-button">
                            Reset Semua Filter
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>
    <div class="dash-pagination">{{ $rooms->links() }}</div>
</x-dashboard-layout>
