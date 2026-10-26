<section id="profile-password" class="profile-card" aria-labelledby="profile-password-title">
    <header class="profile-card-heading">
        <span class="profile-section-icon is-teal"><x-dashboard-icon name="lock" /></span>
        <div><h2 id="profile-password-title">Keamanan akun</h2><p>Perbarui kata sandi untuk menjaga akses akun Anda.</p></div>
    </header>

    @if(session('status') === 'password-updated')
        <div class="profile-success" role="status"><x-dashboard-icon name="check" /> Kata sandi berhasil diperbarui.</div>
    @endif

    @if($errors->getBag('updatePassword')->any())
        <div class="profile-validation-summary" role="alert"><x-dashboard-icon name="info" /><p>Kata sandi belum diperbarui. Periksa isian yang ditandai di bawah.</p></div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="profile-form" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        @method('PUT')

        <x-profile-field name="current_password" id="profile_current_password" label="Kata sandi saat ini" type="password" error-bag="updatePassword" placeholder="Masukkan kata sandi saat ini" required autocomplete="current-password" />
        <div class="profile-field-grid">
            <x-profile-field name="password" id="profile_new_password" label="Kata sandi baru" type="password" error-bag="updatePassword" placeholder="Minimal 8 karakter" hint="Gunakan minimal 8 karakter." required minlength="8" autocomplete="new-password" />
            <x-profile-field name="password_confirmation" id="profile_password_confirmation" label="Konfirmasi kata sandi baru" type="password" error-bag="updatePassword" placeholder="Ulangi kata sandi baru" required minlength="8" autocomplete="new-password" />
        </div>
        <div class="profile-form-footer"><span>Kata sandi tidak ditampilkan kembali setelah disimpan.</span><button class="dash-button is-primary" type="submit" x-bind:disabled="submitting"><span x-text="submitting ? 'Memperbarui…' : 'Perbarui kata sandi'">Perbarui kata sandi</span><x-dashboard-icon name="lock" /></button></div>
    </form>
</section>
