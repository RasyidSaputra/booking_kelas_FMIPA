@props(['title' => 'Dashboard', 'showErrors' => true])
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title }} · Ruang FMIPA</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dash-page" x-data="{ sidebarOpen: false }" x-on:keydown.escape.window="sidebarOpen = false">
        <a class="dash-skip" href="#main-content">Lewati ke konten</a>
        <div class="dash-shell">
            <header class="dash-topbar">
                <div class="dash-topbar-brand">
                    <button type="button" class="dash-icon-button dash-menu-toggle" x-on:click="sidebarOpen = !sidebarOpen" x-bind:aria-expanded="sidebarOpen.toString()" aria-expanded="false" aria-label="Buka atau tutup menu" aria-controls="dashboard-sidebar"><x-dashboard-icon name="menu" /></button>
                    <a href="{{ route('dashboard') }}" class="dash-brand" aria-label="Ruang FMIPA, dashboard">
                        <x-application-logo class="dash-laravel-logo" />
                        <span>RUANG<small>FMIPA UNIVERSITAS RIAU</small></span>
                    </a>
                </div>
                <form method="GET" action="{{ route('rooms.index') }}" class="dash-search" role="search">
                    <label for="navbar-search" class="dash-sr-only">Cari nama, kode, atau lokasi ruangan</label>
                    <x-dashboard-icon name="search" />
                    <input id="navbar-search" type="search" name="q" placeholder="Cari nama, kode, atau lokasi ruangan…" value="{{ is_string(request('q')) ? request('q') : '' }}" maxlength="100">
                    <button type="submit">Cari <span aria-hidden="true">↗</span></button>
                </form>
                <details class="dash-account">
                    <summary><span class="dash-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span class="dash-account-name">{{ auth()->user()->name }}<small>{{ auth()->user()->roleLabel() }}</small></span><x-dashboard-icon name="chevron-down" /></summary>
                    <div class="dash-account-menu">
                        <a href="{{ route('profile.edit') }}"><x-dashboard-icon name="user" /> Profil saya</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-dashboard-icon name="logout" /> Keluar</button></form>
                    </div>
                </details>
            </header>

            <button type="button" class="dash-sidebar-backdrop" x-show.important="sidebarOpen" x-cloak x-on:click="sidebarOpen = false" aria-label="Tutup menu"></button>
            <aside id="dashboard-sidebar" class="dash-sidebar" x-bind:class="{ 'is-open': sidebarOpen }">
                <div class="dash-sidebar-top"><p class="dash-nav-caption">MENU UTAMA</p><button type="button" class="dash-icon-button dash-menu-toggle" x-on:click="sidebarOpen = false" aria-label="Tutup menu"><x-dashboard-icon name="close" /></button></div>
                <nav aria-label="Navigasi utama" class="dash-sidebar-nav">
                    <a href="{{ route('dashboard') }}" @class(['dash-nav-item', 'is-active' => request()->routeIs('dashboard')]) @if(request()->routeIs('dashboard')) aria-current="page" @endif><x-dashboard-icon name="grid" /><span>Dashboard</span></a>
                    <a href="{{ route('bookings.index') }}" @class(['dash-nav-item', 'is-active' => request()->routeIs('bookings.*')]) @if(request()->routeIs('bookings.*')) aria-current="page" @endif><x-dashboard-icon name="list" /><span>Bookingan saya</span></a>
                    <a href="{{ route('rooms.index') }}" @class(['dash-nav-item', 'is-active' => request()->routeIs('rooms.*')]) @if(request()->routeIs('rooms.*')) aria-current="page" @endif><x-dashboard-icon name="building" /><span>Booking ruang</span></a>
                    <a href="{{ route('dashboard') }}#calendar" class="dash-nav-item"><x-dashboard-icon name="calendar" /><span>Kalender ruangan</span></a>
                </nav>

                @can('view-room-master')
                    <p class="dash-nav-caption dash-admin-caption">ADMINISTRASI</p>
                    <nav aria-label="Navigasi admin" class="dash-sidebar-nav">
                        <a href="{{ route('admin.lecture-schedules.index') }}" @class(['dash-nav-item', 'is-active' => request()->routeIs('admin.lecture-schedules.*')]) @if(request()->routeIs('admin.lecture-schedules.*')) aria-current="page" @endif><x-dashboard-icon name="calendar" /><span>Jadwal kuliah mingguan</span></a>
                        <a href="{{ route('admin.rooms.index') }}" @class(['dash-nav-item', 'is-active' => request()->routeIs('admin.rooms.*')]) @if(request()->routeIs('admin.rooms.*')) aria-current="page" @endif><x-dashboard-icon name="building" /><span>Master data kelas</span><span class="dash-admin-tag">Admin</span></a>
                    </nav>
                @endcan

                <div class="dash-sidebar-bottom">
                    <div class="dash-sidebar-tip"><span class="dash-tip-icon"><x-dashboard-icon name="calendar" /></span><strong>Rencanakan lebih awal.</strong><p>Cek jadwal ruangan sebelum mengajukan kegiatan Anda.</p><a href="{{ route('dashboard') }}#calendar">Lihat kalender <span aria-hidden="true">↗</span></a></div>
                    <a href="{{ route('profile.edit') }}" @class(['dash-nav-item', 'is-active' => request()->routeIs('profile.*')]) @if(request()->routeIs('profile.*')) aria-current="page" @endif><x-dashboard-icon name="user" /><span>Pengaturan profil</span></a>
                    <p class="dash-sidebar-footer">Ruang untuk kegiatan bersama.<br>© {{ now()->year }} FMIPA UNRI</p>
                </div>
            </aside>

            <main id="main-content" class="dash-content">
                @if ($showErrors && $errors->any())
                    <div class="dash-error" role="alert"><strong>Periksa kembali filter Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
