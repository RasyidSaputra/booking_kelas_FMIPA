
<x-dashboard-layout :title="'Detail Pengajuan #' . $booking->id">

    @php
        $startsAt = \Carbon\CarbonImmutable::parse($booking->starts_at);
        $endsAt = \Carbon\CarbonImmutable::parse($booking->ends_at);
    @endphp

    <div class="space-y-6">

        {{-- Header dan Navigasi --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-200">
            <div>
                <nav class="text-sm text-gray-500 mb-2">
                    <a href="{{ route('bookings.index') }}"
                       class="hover:text-emerald-700">
                        Bookingan Saya
                    </a>
                    <span class="mx-2">/</span>
                    <span class="font-semibold text-gray-900">
                        Detail Pengajuan
                    </span>
                </nav>

                <h1 class="text-2xl font-bold text-gray-900">
                    Detail Pengajuan
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Informasi lengkap peminjaman ruangan
                    #{{ $booking->id }}
                </p>
            </div>

            <a href="{{ route('bookings.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-semibold
                      text-gray-700 bg-white border border-gray-300
                      rounded-lg hover:bg-gray-50">
                &larr; Kembali
            </a>
        </div>

        {{-- Informasi Status --}}
        <section class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-500">
                        Status Pengajuan
                    </h2>
                    <div class="mt-2">
                        <x-booking-status :status="$booking->status" />
                    </div>
                </div>

                <div class="text-sm text-gray-500">
                    Nomor Pengajuan:
                    <span class="font-bold text-gray-900">
                        #{{ $booking->id }}
                    </span>
                </div>
            </div>
        </section>

        {{-- Informasi Ruangan --}}
        <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-gray-900 mb-4">
                Informasi Ruangan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <p class="text-xs text-gray-500 mb-1">Nama Ruangan</p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $booking->room_name }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-1">Kode Ruangan</p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $booking->room_code }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-1">Lokasi</p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $booking->room_location }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-1">Kapasitas Ruangan</p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $booking->room_capacity }} Kursi
                    </p>
                </div>

            </div>

            <div class="mt-5 pt-4 border-t border-gray-100">
                <a href="{{ route('rooms.show', $booking->room_id) }}"
                   class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                    Lihat Detail Ruangan &rarr;
                </a>
            </div>
        </section>

        {{-- Informasi Jadwal --}}
        <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-gray-900 mb-4">
                Jadwal Peminjaman
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <p class="text-xs text-gray-500 mb-1">
                        Tanggal Peminjaman
                    </p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $startsAt->locale('id')->translatedFormat('l, d F Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-1">
                        Waktu Peminjaman
                    </p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $startsAt->format('H:i') }}
                        - {{ $endsAt->format('H:i') }} WIB
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 mb-1">
                        Jumlah Peserta
                    </p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $booking->participant_count }} Orang
                    </p>
                </div>

            </div>
        </section>

        {{-- Tujuan Peminjaman --}}
        <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-gray-900 mb-3">
                Tujuan Peminjaman
            </h2>

            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                {{ $booking->purpose }}
            </p>
        </section>

        {{-- Informasi Persetujuan --}}
        <section class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-bold text-gray-900 mb-4">
                Informasi Persetujuan Admin
            </h2>

            @if($booking->reviewed_by)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <p class="text-xs text-gray-500 mb-1">
                            Admin Pemeriksa
                        </p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $booking->reviewer_name ?? 'Admin' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">
                            Waktu Pemeriksaan
                        </p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $booking->reviewed_at
                                ? \Carbon\CarbonImmutable::parse($booking->reviewed_at)->locale('id')->translatedFormat('d F Y, H:i') . ' WIB'
                                : 'Belum tersedia' }}
                        </p>
                    </div>

                </div>
            @else
                <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-4">
                    Pengajuan belum diperiksa oleh Admin.
                    Silakan menunggu proses persetujuan.
                </p>
            @endif

            @if($booking->status === 'rejected' && $booking->rejection_reason)
                <div class="mt-5 p-4 rounded-lg bg-red-50 border border-red-200">
                    <p class="text-sm font-bold text-red-800 mb-1">
                        Alasan Penolakan
                    </p>
                    <p class="text-sm text-red-700 whitespace-pre-line">
                        {{ $booking->rejection_reason }}
                    </p>
                </div>
            @endif
        </section>

    </div>

</x-dashboard-layout>
