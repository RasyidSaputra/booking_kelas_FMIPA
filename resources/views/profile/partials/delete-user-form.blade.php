<section class="profile-card profile-danger" aria-labelledby="profile-delete-title">
    <header class="profile-card-heading">
        <span class="profile-section-icon is-danger"><x-dashboard-icon name="user" /></span>
        <div><h2 id="profile-delete-title">Pengaturan akun</h2><p>Kelola penghapusan akun yang sudah tidak digunakan.</p></div>
    </header>

    <details class="profile-delete-details" @if($errors->getBag('userDeletion')->any()) open @endif>
        <summary>Hapus akun saya <x-dashboard-icon name="chevron-down" /></summary>
        <div class="profile-delete-content">
            <p>Penghapusan akun bersifat permanen. Akun yang memiliki riwayat peminjaman atau pemeriksaan tidak dapat dihapus agar riwayat tetap tersimpan.</p>
            <form method="POST" action="{{ route('profile.destroy') }}" class="profile-form">
                @csrf
                @method('DELETE')
                <x-profile-field name="password" id="profile_delete_password" label="Konfirmasi dengan kata sandi saat ini" type="password" error-bag="userDeletion" placeholder="Masukkan kata sandi Anda" required autocomplete="current-password" />
                <button type="submit" class="profile-delete-button">Hapus akun secara permanen</button>
            </form>
        </div>
    </details>
</section>
