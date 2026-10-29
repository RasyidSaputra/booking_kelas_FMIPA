<x-dashboard-layout title="Bookingan saya">
    <x-booking-permission-notice class="mb-5" />
    <div class="dash-page-heading"><div><p class="dash-eyebrow">RENCANA KEGIATAN ANDA</p><h1>Bookingan saya</h1><p>Pantau status dan riwayat peminjaman ruangan Anda.</p></div><a class="dash-button is-primary" href="{{ route('rooms.index') }}"><x-dashboard-icon name="building" />Jelajahi ruangan</a></div>

    @if(session('success'))
        <div class="p-4 mb-5 text-sm font-medium text-emerald-800 bg-emerald-50 rounded-lg border border-emerald-200 flex items-center gap-2" role="alert">
            <x-dashboard-icon name="check" class="w-5 h-5 text-emerald-600 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 mb-5 text-sm font-medium text-red-800 bg-red-50 rounded-lg border border-red-200 flex items-center gap-2" role="alert">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <section class="dash-table-card" aria-label="Daftar peminjaman saya">
        @if ($bookings->isEmpty())
            <div class="dash-empty"><x-dashboard-icon name="list" /><h2>Belum ada booking</h2><p>Pengajuan peminjaman Anda akan muncul di sini.</p><a class="dash-button is-primary" href="{{ route('rooms.index') }}">Lihat ruangan <x-dashboard-icon name="arrow" /></a></div>
        @else
            <div class="dash-table-scroll"><table class="dash-table"><thead><tr><th scope="col">Ruangan</th><th scope="col">Keperluan</th><th scope="col">Waktu pemakaian</th><th scope="col">Peserta</th><th scope="col">Status</th></tr></thead><tbody>
                @foreach ($bookings as $booking)
                    <tr><td><strong>{{ $booking->room_name }}</strong><small>{{ $booking->room_code }}</small></td><td class="dash-purpose">{{ $booking->purpose }}@if($booking->status === 'rejected' && $booking->rejection_reason)<small>Alasan: {{ $booking->rejection_reason }}</small>@endif</td><td><strong>{{ \Carbon\CarbonImmutable::parse($booking->starts_at)->locale('id')->translatedFormat('d M Y, H:i') }}</strong><small>sampai {{ \Carbon\CarbonImmutable::parse($booking->ends_at)->locale('id')->translatedFormat('d M Y, H:i') }}</small></td><td>{{ $booking->participant_count }} orang</td><td><x-booking-status :status="$booking->status" /></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
    <div class="dash-pagination">{{ $bookings->links() }}</div>
</x-dashboard-layout>
