<section id="profile-information" class="profile-card" aria-labelledby="profile-information-title">
    <header class="profile-card-heading">
        <span class="profile-section-icon"><x-dashboard-icon name="user" /></span>
        <div><h2 id="profile-information-title">Informasi profil</h2><p>Perbarui nama, identitas, dan alamat email Anda.</p></div>
    </header>

    @if(session('status') === 'profile-updated')
        <div class="profile-success" role="status"><x-dashboard-icon name="check" /> Perubahan profil berhasil disimpan.</div>
    @endif

    @if($errors->getBag('default')->any())
        <div class="profile-validation-summary" role="alert"><x-dashboard-icon name="info" /><div><strong>Profil belum tersimpan.</strong><ul>@foreach($errors->getBag('default')->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>

    <form method="POST" action="{{ route('profile.update') }}" class="profile-form" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        @method('PATCH')

        <x-profile-field name="name" label="Nama lengkap" :value="$user->name" placeholder="Nama sesuai identitas Anda" required autocomplete="name" maxlength="255" />

        <div class="profile-field-grid">
            <x-profile-field name="identity_number" label="NIM / NIP" :value="$user->identity_number" placeholder="Masukkan NIM atau NIP" hint="Gunakan identitas mahasiswa atau pegawai Anda." required maxlength="30" autocapitalize="off" spellcheck="false" />
            <x-profile-field name="email" label="Alamat email" type="email" :value="$user->email" placeholder="nama@contoh.ac.id" hint="Gunakan alamat email aktif dengan huruf kecil." required autocomplete="email" maxlength="255" />
        </div>

        @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="profile-verification"><x-dashboard-icon name="info" /><div><p>Alamat email Anda belum diverifikasi.</p><button type="submit" form="send-verification" class="dash-text-link">Kirim ulang email verifikasi</button></div></div>
        @endif

        <div class="profile-role-info">
            <span class="profile-section-icon is-muted"><x-dashboard-icon name="shield" /></span>
            <div><span class="profile-role-label">Role akun</span><strong>{{ $user->roleLabel() }}</strong><p>Role dikelola oleh pengelola sistem dan tidak dapat diubah melalui halaman profil.</p></div>
            <span class="profile-readonly-label"><x-dashboard-icon name="lock" />Hanya informasi</span>
        </div>

        <div class="profile-form-footer"><span>Pastikan data Anda sudah benar sebelum menyimpan.</span><button class="dash-button is-primary" type="submit" x-bind:disabled="submitting"><span x-text="submitting ? 'Menyimpan…' : 'Simpan perubahan'">Simpan perubahan</span><x-dashboard-icon name="check" /></button></div>
    </form>
</section>
