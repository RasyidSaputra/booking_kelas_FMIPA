<x-dashboard-layout :title="$room->name . ' · Detail Ruangan'">
    <div class="space-y-6" x-data="{
        activePlanTab: 'schematic',
        bookingModalOpen: {{ ($errors->any() || session('error') || request('book') === '1') ? 'true' : 'false' }}
    }">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 text-sm font-medium text-emerald-800 bg-emerald-50 rounded-lg border border-emerald-200 flex items-center gap-2" role="alert">
                <x-dashboard-icon name="check" class="w-5 h-5 text-emerald-600 shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 text-sm font-medium text-red-800 bg-red-50 rounded-lg border border-red-200 flex items-center gap-2" role="alert">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Breadcrumb & Navigasi Atas --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pb-2 border-b border-gray-200">
            <nav class="flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
                <a href="{{ route('rooms.index') }}" class="hover:text-emerald-700 transition-colors flex items-center gap-1 font-medium">
                    <x-dashboard-icon name="building" class="w-4 h-4" />
                    <span>Daftar Ruangan</span>
                </a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900 font-semibold truncate">{{ $room->name }}</span>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                    <x-dashboard-icon name="chevron-left" class="w-3.5 h-3.5" />
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        {{-- Header Ruangan & FITUR 1: Indikator Status Instan --}}
        <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-5">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold tracking-wider uppercase bg-gray-100 text-gray-800">
                            {{ $room->code }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500 font-medium">
                            <x-dashboard-icon name="users" class="w-3.5 h-3.5" />
                            Kapasitas {{ $room->capacity }} Kursi
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs text-gray-500 font-medium">
                            <x-dashboard-icon name="pin" class="w-3.5 h-3.5" />
                            {{ $room->location }}
                        </span>
                    </div>

                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">
                            {{ $room->name }}
                        </h1>
                        @if($room->description)
                            <p class="mt-1 text-sm text-gray-600 max-w-2xl leading-relaxed">
                                {{ $room->description }}
                            </p>
                        @endif
                    </div>

                    {{-- FITUR 1: Indikator Status Instan (Badge Dinamis) --}}
                    <div class="pt-1">
                        @if($currentBooking)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <span>Sedang Digunakan hingga {{ \Carbon\CarbonImmutable::parse($currentBooking->ends_at)->format('H:i') }} WIB</span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200 shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                <span>Saat ini: Tersedia</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- FITUR 2: Visualisasi Kondisi Fasilitas (Grid UI) --}}
        <section class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Kondisi Fasilitas Ruangan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pemantauan kelayakan sarana & prasarana pembelajaran real-time.</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-slate-500 self-start sm:self-auto">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Normal
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                        Rusak / Perbaikan
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
                {{-- Fasilitas 1: Air Conditioner (AC) --}}
                <div class="flex flex-col justify-between p-4 rounded-lg border border-slate-200 bg-white shadow-xs hover:border-slate-300 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 text-slate-500 border border-slate-200/70 flex items-center justify-center shrink-0">
                            {{-- Ikon AC (Minimalis) --}}
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.5 4h5M4 8h16M7 12h10M5 16h14M8 20h8" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Normal
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-slate-800">AC (Pendingin Ruang)</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">2 unit split inverter · Dingin optimal</p>
                    </div>
                </div>

                {{-- Fasilitas 2: Proyektor HDMI --}}
                <div class="flex flex-col justify-between p-4 rounded-lg border border-slate-200 bg-white shadow-xs hover:border-slate-300 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 text-slate-500 border border-slate-200/70 flex items-center justify-center shrink-0">
                            {{-- Ikon Proyektor (Minimalis) --}}
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="11" rx="2" />
                                <circle cx="8" cy="11.5" r="2.5" />
                                <path d="M16 10h2m-2 3h2M6 21l3-4m9 4-3-4" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Normal
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-slate-800">Proyektor & Layar</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">HDMI & VGA ready · Resolusi FHD</p>
                    </div>
                </div>

                {{-- Fasilitas 3: Papan Tulis (Whiteboard) --}}
                <div class="flex flex-col justify-between p-4 rounded-lg border border-slate-200 bg-white shadow-xs hover:border-slate-300 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 text-slate-500 border border-slate-200/70 flex items-center justify-center shrink-0">
                            {{-- Ikon Papan Tulis (Minimalis) --}}
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="14" rx="2" />
                                <path d="M8 21h8m-4-4v4M7 13h4" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Normal
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-slate-800">Papan Tulis (Whiteboard)</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Permukaan bersih · Spidol & penghapus tersedia</p>
                    </div>
                </div>

                {{-- Fasilitas 4: Stopkontak (Kondisi Rusak / Perbaikan) --}}
                <div class="flex flex-col justify-between p-4 rounded-lg border border-slate-200 bg-white shadow-xs hover:border-slate-300 transition-colors">
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-9 h-9 rounded-lg bg-slate-50 text-slate-500 border border-slate-200/70 flex items-center justify-center shrink-0">
                            {{-- Ikon Stopkontak (Minimalis) --}}
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22v-5M9 7V2m6 5V2M6 13V8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v5a6 6 0 0 1-6 6 6 6 0 0 1-6-6Z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-600 border border-rose-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            Rusak / Perbaikan
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-sm font-medium text-slate-800">Stopkontak Dinding & Meja</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Baris belakang bermasalah (laporan teknisi #402)</p>
                    </div>
                </div>

                {{-- Fasilitas Tambahan dari Database (Jika Ada) --}}
                @foreach($facilities as $facility)
                    @if(!in_array(strtolower($facility->name), ['ac', 'proyektor', 'papan tulis', 'stopkontak']))
                        <div class="flex flex-col justify-between p-4 rounded-lg border border-slate-200 bg-white shadow-xs hover:border-slate-300 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="w-9 h-9 rounded-lg bg-slate-50 text-slate-500 border border-slate-200/70 flex items-center justify-center shrink-0">
                                    <x-dashboard-icon name="grid" class="w-4 h-4" />
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Normal
                                </span>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-sm font-medium text-slate-800">{{ $facility->name }}</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Fasilitas terdaftar</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- FITUR 3: Denah atau Tata Letak Ruangan Sederhana --}}
        <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Denah & Tata Letak Ruangan</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Visualisasi skematik susunan meja, papan tulis, dan titik akses stopkontak listrik.</p>
                </div>
                {{-- Tab Switcher: Skematik CSS vs Placeholder Gambar --}}
                <div class="flex items-center p-1 bg-gray-100 rounded-lg self-start">
                    <button type="button"
                        x-on:click="activePlanTab = 'schematic'"
                        :class="activePlanTab === 'schematic' ? 'bg-white text-gray-900 shadow-xs font-semibold' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 text-xs rounded-md transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        <span>Skematik CSS</span>
                    </button>
                    <button type="button"
                        x-on:click="activePlanTab = 'blueprint'"
                        :class="activePlanTab === 'blueprint' ? 'bg-white text-gray-900 shadow-xs font-semibold' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 text-xs rounded-md transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Placeholder Gambar</span>
                    </button>
                </div>
            </div>

            {{-- 3A. Skematik Tata Letak CSS (Bersih, Responsif, Interaktif) --}}
            <div x-show="activePlanTab === 'schematic'" class="mt-5 space-y-4">
                <div class="relative w-full max-w-3xl mx-auto p-5 md:p-8 bg-slate-50/70 border-2 border-slate-200 rounded-xl">
                    {{-- Aksen Dinding Depan & Papan Tulis --}}
                    <div class="mb-8">
                        <div class="text-center mb-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">SISI DEPAN / MIMBAR</span>
                        </div>
                        <div class="relative flex items-center justify-center">
                            {{-- Papan Tulis Utama --}}
                            <div class="w-3/5 py-2.5 px-4 bg-emerald-800 text-white rounded shadow-sm text-center font-semibold text-xs border-2 border-emerald-950 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="18" height="14" rx="2"/></svg>
                                <span>Papan Tulis & Layar Proyektor</span>
                            </div>
                            {{-- Stopkontak Dinding Depan --}}
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-1 px-2 py-1 bg-amber-50 border border-amber-300 rounded text-[10px] font-bold text-amber-800 shadow-2xs" title="Stopkontak Dinding Depan (Normal)">
                                🔌 <span>Stopkontak</span>
                            </div>
                        </div>
                    </div>

                    {{-- Meja Dosen / Pengajar --}}
                    <div class="flex justify-center mb-8">
                        <div class="relative w-48 py-2 px-3 bg-amber-100 border border-amber-300 rounded text-center text-xs font-semibold text-amber-900 shadow-2xs">
                            <span>Meja Dosen & Podium</span>
                            <span class="absolute -top-2 -right-2 px-1.5 py-0.2 bg-white border border-amber-400 rounded-full text-[9px] text-amber-700 font-bold" title="Stopkontak Pengajar">🔌</span>
                        </div>
                    </div>

                    {{-- Area Meja & Kursi Mahasiswa (Grid 2 Kolom, 3 Baris) --}}
                    <div class="relative grid grid-cols-2 gap-x-8 gap-y-4 max-w-xl mx-auto py-2">
                        {{-- Indikator Stopkontak Dinding Kiri --}}
                        <div class="absolute -left-3 top-1/2 -translate-y-1/2 -translate-x-full hidden md:flex flex-col items-center gap-1 text-[10px] text-gray-500 font-medium">
                            <span class="p-1 bg-amber-100 text-amber-800 border border-amber-300 rounded text-xs">🔌</span>
                            <span>Stopkontak</span>
                        </div>

                        {{-- Baris 1 Mahasiswa --}}
                        <div class="p-2.5 bg-white border border-gray-300 rounded text-center shadow-2xs">
                            <span class="text-xs font-medium text-gray-800">Meja 01 (Kiri)</span>
                            <span class="block text-[10px] text-gray-400">8 Kursi</span>
                        </div>
                        <div class="p-2.5 bg-white border border-gray-300 rounded text-center shadow-2xs">
                            <span class="text-xs font-medium text-gray-800">Meja 02 (Kanan)</span>
                            <span class="block text-[10px] text-gray-400">8 Kursi</span>
                        </div>

                        {{-- Baris 2 Mahasiswa --}}
                        <div class="p-2.5 bg-white border border-gray-300 rounded text-center shadow-2xs">
                            <span class="text-xs font-medium text-gray-800">Meja 03 (Kiri)</span>
                            <span class="block text-[10px] text-gray-400">8 Kursi</span>
                        </div>
                        <div class="p-2.5 bg-white border border-gray-300 rounded text-center shadow-2xs">
                            <span class="text-xs font-medium text-gray-800">Meja 04 (Kanan)</span>
                            <span class="block text-[10px] text-gray-400">8 Kursi</span>
                        </div>

                        {{-- Baris 3 Mahasiswa (Dengan Catatan Stopkontak Belakang) --}}
                        <div class="p-2.5 bg-white border border-gray-300 rounded text-center shadow-2xs">
                            <span class="text-xs font-medium text-gray-800">Meja 05 (Kiri)</span>
                            <span class="block text-[10px] text-gray-400">8 Kursi</span>
                        </div>
                        <div class="p-2.5 bg-white border border-gray-300 rounded text-center shadow-2xs relative">
                            <span class="text-xs font-medium text-gray-800">Meja 06 (Kanan)</span>
                            <span class="block text-[10px] text-gray-400">8 Kursi</span>
                            <span class="absolute -bottom-2 -right-2 px-1.5 py-0.2 bg-red-100 border border-red-300 rounded-full text-[9px] text-red-700 font-bold" title="Stopkontak Rusak/Perbaikan">⚠️ 🔌</span>
                        </div>

                        {{-- Indikator Stopkontak Dinding Kanan --}}
                        <div class="absolute -right-3 top-1/2 -translate-y-1/2 translate-x-full hidden md:flex flex-col items-center gap-1 text-[10px] text-gray-500 font-medium">
                            <span class="p-1 bg-red-100 text-red-800 border border-red-300 rounded text-xs">⚠️</span>
                            <span>Perbaikan</span>
                        </div>
                    </div>

                    {{-- Dinding Belakang & Pintu Akses --}}
                    <div class="mt-8 pt-4 border-t border-dashed border-gray-300 flex items-center justify-between text-xs text-gray-500">
                        <span class="text-[10px] uppercase tracking-widest text-gray-400">DINDING BELAKANG</span>
                        <div class="flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 rounded font-semibold text-xs">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"/></svg>
                            <span>Pintu Masuk & Keluar</span>
                        </div>
                    </div>
                </div>

                {{-- Legenda Denah Skematik --}}
                <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-gray-600 pt-2">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 bg-emerald-800 rounded-xs"></span>
                        <span>Papan Tulis & Layar</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 bg-amber-100 border border-amber-300 rounded-xs"></span>
                        <span>Meja Dosen</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 bg-white border border-gray-300 rounded-xs"></span>
                        <span>Meja Mahasiswa</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="px-1 bg-amber-100 border border-amber-300 rounded text-[10px]">🔌</span>
                        <span>Stopkontak Normal</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="px-1 bg-red-100 border border-red-300 rounded text-[10px]">⚠️</span>
                        <span>Stopkontak Perbaikan</span>
                    </span>
                </div>
            </div>

            {{-- 3B. Placeholder Image Card yang Elegan (Sesuai Permintaan) --}}
            <div x-show="activePlanTab === 'blueprint'" x-cloak class="mt-5">
                <div class="aspect-video w-full max-w-3xl mx-auto bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center p-8 text-center text-gray-500 hover:bg-gray-100/70 transition-colors cursor-pointer group">
                    <div class="w-14 h-14 rounded-full bg-white shadow-xs border border-gray-200 flex items-center justify-center text-gray-400 group-hover:text-emerald-600 group-hover:border-emerald-200 transition-colors">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-sm font-bold text-gray-800 group-hover:text-emerald-700 transition-colors">
                        Denah Ruangan & Posisi Stopkontak
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 max-w-sm">
                        Area gambar teknis / blueprint arsitektur tata letak kelas. Klik untuk mengunggah atau mengganti berkas denah resmi (JPG, PNG, atau PDF).
                    </p>
                    <span class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 shadow-2xs group-hover:bg-emerald-50 group-hover:text-emerald-700 group-hover:border-emerald-300 transition-colors">
                        <x-dashboard-icon name="plus" class="w-3.5 h-3.5" />
                        Pilih Gambar Denah
                    </span>
                </div>
            </div>
        </section>

        {{-- Jadwal Pemakaian Hari Ini & Tombol Booking --}}
        <section class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Jadwal Pemakaian Hari Ini</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                </div>
                <a href="{{ route('dashboard') }}#calendar" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                    Lihat Kalender Lengkap &rarr;
                </a>
            </div>

            <div class="mt-4">
                @if($todayBookings->isEmpty())
                    <div class="text-center py-6 bg-gray-50/50 rounded-lg border border-dashed border-gray-200">
                        <p class="text-xs font-medium text-gray-500">Belum ada kuliah atau peminjaman disetujui untuk hari ini. Ruangan dapat diajukan dengan surat izin atau surat peminjaman ruangan.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($todayBookings as $b)
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-900">{{ \Carbon\CarbonImmutable::parse($b->starts_at)->format('H:i') }} - {{ \Carbon\CarbonImmutable::parse($b->ends_at)->format('H:i') }} WIB</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Disetujui</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-0.5">{{ $b->purpose }} · Oleh {{ $b->user_name }}</p>
                                </div>
                                <span class="text-xs text-gray-400">{{ $b->participant_count }} peserta</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tombol Aksi Peminjaman --}}
            <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <span class="text-xs text-gray-500">Ingin meminjam ruangan ini untuk kegiatan kuliah atau seminar?</span>
                <button type="button"
                    x-on:click="bookingModalOpen = true"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-700 text-white rounded-lg text-xs font-bold hover:bg-emerald-800 transition-colors shadow-xs cursor-pointer">
                    <x-dashboard-icon name="calendar" class="w-4 h-4" />
                    <span>Ajukan Peminjaman Ruang Ini</span>
                </button>
            </div>
        </section>

        {{-- FITUR 1: Modal Form Peminjaman Ruangan --}}
        <div x-show="bookingModalOpen"
            x-cloak
            x-on:keydown.escape.window="bookingModalOpen = false"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-booking-title" role="dialog" aria-modal="true">
            {{-- Backdrop Blur & Dim --}}
            <div x-show="bookingModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="bookingModalOpen = false"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="bookingModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-on:click.stop
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">

                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between p-5 border-b border-gray-100 bg-slate-50/70">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-emerald-100 text-emerald-800 mb-1">
                                {{ $room->code }}
                            </span>
                            <h3 class="text-base font-bold text-gray-900" id="modal-booking-title">
                                Form Pengajuan Peminjaman Ruangan
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $room->name }} · {{ $room->location }}
                            </p>
                        </div>
                        <button type="button"
                            x-on:click="bookingModalOpen = false"
                            class="text-gray-400 hover:text-gray-500 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-dashboard-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Form Peminjaman --}}
                    <form method="POST" action="{{ route('bookings.store') }}" class="p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        <x-booking-permission-notice />

                        {{-- Alert jika ada error bentrok atau validasi --}}
                        @if(session('error'))
                            <div class="p-3 text-xs font-semibold text-red-800 bg-red-50 rounded-lg border border-red-200 flex items-start gap-2">
                                <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="p-3 text-xs text-red-800 bg-red-50 rounded-lg border border-red-200">
                                <strong class="font-bold">Mohon perbaiki isian berikut:</strong>
                                <ul class="list-disc list-inside mt-1 space-y-0.5">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Input Tanggal Pemakaian --}}
                        <div>
                            <label for="booking_date" class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Pemakaian <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                id="booking_date"
                                name="booking_date"
                                value="{{ old('booking_date', request('date', today()->toDateString())) }}"
                                min="{{ today()->toDateString() }}"
                                required
                                class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        {{-- Waktu Mulai & Waktu Selesai --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="start_time" class="block text-xs font-bold text-gray-700 mb-1">
                                    Waktu Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="time"
                                    id="start_time"
                                    name="start_time"
                                    value="{{ old('start_time', '08:00') }}"
                                    min="07:30"
                                    max="18:00"
                                    required
                                    class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label for="end_time" class="block text-xs font-bold text-gray-700 mb-1">
                                    Waktu Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="time"
                                    id="end_time"
                                    name="end_time"
                                    value="{{ old('end_time', '10:00') }}"
                                    min="07:30"
                                    max="18:00"
                                    required
                                    class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-amber-50/80 border border-amber-200 text-amber-800 text-[11px] flex items-center gap-2">
                            <span class="text-sm">⏰</span>
                            <span>Jam operasional ruangan: <strong>07:30 – 18:00 WIB</strong>. Gedung tidak dapat digunakan setelah pukul 18:00.</span>
                        </div>

                        {{-- Jumlah Peserta --}}
                        <div>
                            <label for="participant_count" class="block text-xs font-bold text-gray-700 mb-1">
                                Estimasi Jumlah Peserta (Orang)
                            </label>
                            <input type="number"
                                id="participant_count"
                                name="participant_count"
                                value="{{ old('participant_count', $room->capacity) }}"
                                min="1"
                                max="{{ max($room->capacity, 200) }}"
                                placeholder="Maks. {{ $room->capacity }} orang"
                                class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                            <span class="text-[11px] text-gray-400 mt-0.5 block">Kapasitas ruang: {{ $room->capacity }} kursi</span>
                        </div>

                        {{-- Tujuan Peminjaman --}}
                        <div>
                            <label for="purpose" class="block text-xs font-bold text-gray-700 mb-1">
                                Tujuan Peminjaman <span class="text-red-500">*</span>
                            </label>
                            <textarea id="purpose"
                                name="purpose"
                                rows="3"
                                required
                                placeholder="Jelaskan keperluan Anda, misalnya: Kuliah Tambahan Kalkulus II, Seminar Proposal, Ujian Praktikum..."
                                class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">{{ old('purpose') }}</textarea>
                        </div>

                        {{-- Footer Aksi Form --}}
                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                            <button type="button"
                                x-on:click="bookingModalOpen = false"
                                class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                <x-dashboard-icon name="check" class="w-4 h-4" />
                                <span>Kirim Pengajuan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
