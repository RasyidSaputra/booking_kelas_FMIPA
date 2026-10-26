<x-auth-layout page="register">
    <div class="auth-form-heading">
        <span class="auth-form-kicker">MULAI RENCANA ANDA</span>
        <h2 id="auth-form-title">Satu akun, banyak ruang.</h2>
        <p>Daftarkan diri untuk mengajukan peminjaman ruangan.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form auth-register-form" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <x-auth-field name="name" label="Nama lengkap" placeholder="Nama sesuai identitas Anda" required autofocus autocomplete="name" maxlength="255" />
        <x-auth-field name="identity_number" label="NIM / NIP" placeholder="Masukkan NIM atau NIP Anda" required maxlength="30" autocapitalize="off" spellcheck="false" />
        <x-auth-field name="email" label="Alamat email" type="email" placeholder="nama@contoh.ac.id" required autocomplete="username" maxlength="255" />

        <div class="auth-password-grid">
            <x-auth-field name="password" label="Kata sandi" type="password" placeholder="Minimal 8 karakter" hint="Gunakan minimal 8 karakter." required minlength="8" autocomplete="new-password" />
            <x-auth-field name="password_confirmation" label="Konfirmasi kata sandi" type="password" placeholder="Ulangi kata sandi" required minlength="8" autocomplete="new-password" />
        </div>

        <p class="auth-registration-note"><span aria-hidden="true">ⓘ</span> Akun baru terdaftar sebagai mahasiswa.</p>

        <button class="auth-submit" type="submit" x-bind:disabled="submitting">
            <span x-text="submitting ? 'Membuat akun…' : 'Buat akun'">Buat akun</span>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>

    <p class="auth-switch">Sudah memiliki akun? <a class="auth-link" href="{{ route('login') }}">Masuk di sini <span aria-hidden="true">↗</span></a></p>
</x-auth-layout>
