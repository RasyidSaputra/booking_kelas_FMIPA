@props(['bookings', 'date'])
@if ($bookings->isEmpty())
    <p class="dash-no-schedule"><span class="dash-dot is-quiet"></span> Belum ada jadwal disetujui</p>
@else
    <ul class="dash-room-slots" aria-label="Jam pemakaian ruangan">
        @foreach ($bookings as $booking)
            @php
                $start = \Carbon\CarbonImmutable::parse($booking->starts_at)->max($date->startOfDay());
                $end = \Carbon\CarbonImmutable::parse($booking->ends_at)->min($date->startOfDay()->addDay());
            @endphp
            <li><x-dashboard-icon name="clock" /><span>{{ $start->format('H:i') }} – {{ $end->isSameDay($date) ? $end->format('H:i') : '24:00' }}</span></li>
        @endforeach
    </ul>
@endif
