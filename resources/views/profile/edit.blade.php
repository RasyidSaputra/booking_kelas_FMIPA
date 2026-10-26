<x-dashboard-layout title="Profil saya" :show-errors="false">
    <div class="dash-page-heading">
        <div>
            <p class="dash-eyebrow">AKUN DAN IDENTITAS ANDA</p>
            <h1>Profil saya</h1>
            <p>Kelola informasi pribadi dan keamanan akun Anda.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="dash-button"><x-dashboard-icon name="chevron-left" /> Kembali ke dashboard</a>
    </div>

    @if(session('status') === 'verification-link-sent')
        <div class="profile-success" role="status"><x-dashboard-icon name="check" /> Tautan verifikasi baru sudah dikirim ke alamat email Anda.</div>
    @endif

    <div class="profile-layout">
        <aside class="profile-summary" aria-labelledby="profile-summary-title">
            <div class="profile-summary-top">
                <span class="profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <h2 id="profile-summary-title">{{ $user->name }}</h2>
                <p class="profile-summary-email">{{ $user->email }}</p>
                <span class="profile-role-badge"><x-dashboard-icon name="shield" />{{ $user->roleLabel() }}</span>
            </div>
            <dl class="profile-summary-details">
                <div><dt>NIM / NIP</dt><dd>{{ $user->identity_number ?: 'Belum diisi' }}</dd></div>
                <div><dt>Bergabung sejak</dt><dd>{{ $user->created_at?->locale('id')->translatedFormat('d F Y') ?? '—' }}</dd></div>
            </dl>
            <nav class="profile-summary-nav" aria-label="Bagian pengaturan profil">
                <a href="#profile-information"><x-dashboard-icon name="user" />Informasi profil<x-dashboard-icon name="chevron-right" /></a>
                <a href="#profile-password"><x-dashboard-icon name="lock" />Keamanan akun<x-dashboard-icon name="chevron-right" /></a>
            </nav>
            <div class="profile-summary-note"><x-dashboard-icon name="info" /><p>Gunakan identitas yang sesuai agar pengajuan kegiatan mudah dikenali.</p></div>
        </aside>

        <div class="profile-sections">
            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-dashboard-layout>
