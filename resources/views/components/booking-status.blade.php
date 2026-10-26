@props(['status'])
@php
    $label = match ($status) {
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        default => 'Tidak diketahui',
    };
@endphp
<span @class(['dash-status', 'is-approved' => $status === 'approved', 'is-pending' => $status === 'pending', 'is-rejected' => $status === 'rejected'])>{{ $label }}</span>
