<x-dashboard-layout title="Bookingan saya">
    <div class="dash-page-heading"><div><p class="dash-eyebrow">RENCANA KEGIATAN ANDA</p><h1>Bookingan saya</h1><p>Pantau status dan riwayat peminjaman ruangan Anda.</p></div><a class="dash-button is-primary" href="{{ route('rooms.index') }}"><x-dashboard-icon name="building" />Jelajahi ruangan</a></div>
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
