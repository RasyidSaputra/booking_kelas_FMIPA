@props(['page' => 'login'])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Akses layanan peminjaman ruang kelas FMIPA Universitas Riau.">
        <title>{{ $page === 'register' ? 'Daftar akun' : 'Masuk' }} · Ruang FMIPA</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page">
        <div class="auth-shell">
            <header class="auth-header">
                <a class="auth-brand" href="{{ url('/') }}" aria-label="Ruang FMIPA, beranda">
                    <span class="auth-brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none">
                            <path d="M7 25V7h18v18M4 25h24M13 25v-7h6v7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 12h2m4 0h2" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="auth-brand-name">RUANG<span>FMIPA UNIVERSITAS RIAU</span></span>
                </a>
                <nav class="auth-nav" aria-label="Akses akun">
                    <a href="{{ route('login') }}" @class(['auth-nav-link', 'is-active' => $page === 'login']) @if ($page === 'login') aria-current="page" @endif>Masuk</a>
                    <a href="{{ route('register') }}" @class(['auth-nav-link', 'is-active' => $page === 'register']) @if ($page === 'register') aria-current="page" @endif>Daftar akun <span aria-hidden="true">↗</span></a>
                </nav>
            </header>

            <main class="auth-main">
                <section class="auth-story" aria-labelledby="auth-story-title">
                    <div>
                        <p class="auth-eyebrow"><span></span> RUANG KAMPUS, DALAM JANGKAUAN</p>
                        <h1 id="auth-story-title">Ruang untuk<br> ide <span>berikutnya.</span></h1>
                        <p class="auth-story-description">Dari diskusi kecil hingga kegiatan bersama.<br class="auth-desktop-break"> Mulai rencanakan ruang Anda di FMIPA.</p>
                    </div>

                    <div class="auth-campus" aria-hidden="true">
                        <div class="auth-campus-heading">
                            <span class="auth-campus-label"><span></span> Sudut kampus Anda</span>
                            <span class="auth-floor-label">FMIPA / UNRI</span>
                        </div>
                        <svg class="auth-floorplan" viewBox="0 0 480 275" fill="none">
                            <defs>
                                <pattern id="campus-grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M20 0H0V20" stroke="#e9eeef" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="480" height="275" fill="url(#campus-grid)"/>
                            <g transform="translate(32 23)">
                                <rect x="1" y="1" width="414" height="224" rx="3" fill="#fff" stroke="#b5c5ce" stroke-width="3"/>
                                <path d="M1 95h414M1 137h414M146 1v94M290 1v94M200 137v88" stroke="#b5c5ce" stroke-width="3"/>
                                <rect x="12" y="12" width="121" height="72" rx="4" fill="#edf6f5"/>
                                <rect x="158" y="12" width="119" height="72" rx="4" fill="#f0f4f6"/>
                                <rect x="303" y="12" width="101" height="72" rx="4" fill="#edf6f5"/>
                                <rect x="12" y="150" width="175" height="62" rx="4" fill="#f0f4f6"/>
                                <rect x="213" y="150" width="191" height="62" rx="4" fill="#e0f3ef"/>
                                <g fill="#fff" stroke="#b8d4d0" stroke-width="1.5">
                                    <rect x="30" y="29" width="85" height="12" rx="3"/><rect x="30" y="52" width="85" height="12" rx="3"/>
                                    <rect x="322" y="29" width="63" height="12" rx="3"/><rect x="322" y="52" width="63" height="12" rx="3"/>
                                    <rect x="236" y="164" width="61" height="32" rx="7"/><rect x="318" y="164" width="61" height="32" rx="7"/>
                                </g>
                                <g fill="#d6e2e8">
                                    <rect x="178" y="30" width="80" height="12" rx="3"/><rect x="178" y="53" width="80" height="12" rx="3"/>
                                    <rect x="35" y="167" width="129" height="27" rx="7"/>
                                </g>
                                <path d="M62 95h26m116 0h26m110 0h26M88 137h26m167 0h26" stroke="#fff" stroke-width="5"/>
                                <path d="M62 95V71a24 24 0 0 1 24 24m118 0V71a24 24 0 0 1 24 24m53 42v24a24 24 0 0 0 24-24" stroke="#a4b9c3" stroke-width="1.2"/>
                                <path d="M24 116h366" stroke="#c8dcda" stroke-dasharray="4 6" stroke-width="2"/>
                                <circle cx="351" cy="180" r="21" fill="#0e9e91" fill-opacity=".12"/>
                                <circle cx="351" cy="180" r="13" fill="#07998c"/>
                                <path d="m346 180 3 3 6-6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                        </svg>
                        <div class="auth-plan-note">
                            <span class="auth-note-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="5" width="16" height="15" rx="3" stroke="currentColor" stroke-width="1.6"/><path d="M8 3v4m8-4v4M4 10h16m-12 5 2 2 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span><strong>Satu tempat, lebih terencana.</strong><small>Temukan ruang. Ajukan. Pantau statusnya.</small></span>
                        </div>
                        <p class="auth-illustration-caption">ILUSTRASI TATA RUANG KAMPUS</p>
                    </div>

                    <div class="auth-story-bottom">
                        <span class="auth-story-rule"></span>
                        <p>Untuk kegiatan yang lebih tertata,<br><strong>dan ruang yang dimanfaatkan bersama.</strong></p>
                    </div>
                </section>

                <section class="auth-form-panel" aria-labelledby="auth-form-title">
                    <div class="auth-form-content {{ $page === 'register' ? 'auth-form-content-register' : '' }}">
                        {{ $slot }}
                    </div>
                </section>
            </main>

            <footer class="auth-footer">
                <p>© {{ now()->year }} FMIPA Universitas Riau</p>
                <span class="auth-footer-label"><span></span> Sistem Peminjaman Ruangan</span>
            </footer>
        </div>
    </body>
</html>
