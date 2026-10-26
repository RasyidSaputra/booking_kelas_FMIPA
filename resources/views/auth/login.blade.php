<x-auth-layout page="login">
    <div class="auth-form-heading">
        <span class="auth-form-kicker">SELAMAT DATANG DI RUANG</span>
        <h2 id="auth-form-title">Senang Anda kembali.</h2>
        <p>Masuk untuk melanjutkan rencana kegiatan Anda.</p>
    </div>

    @if (session('status'))
        <div class="auth-status" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <x-auth-field name="email" label="Alamat email" type="email" placeholder="nama@contoh.ac.id" required autofocus autocomplete="username" maxlength="255" />
        <x-auth-field name="password" label="Kata sandi" type="password" placeholder="Masukkan kata sandi Anda" required autocomplete="current-password" />

        <div class="auth-form-options">
            <label for="remember" class="auth-checkbox-label">
                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                <span>Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>

        <button class="auth-submit" type="submit" x-bind:disabled="submitting">
            <span x-text="submitting ? 'Sedang masuk…' : 'Masuk'">Masuk</span>
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </form>

    <p class="auth-switch">Belum memiliki akun? <a class="auth-link" href="{{ route('register') }}">Daftar sekarang <span aria-hidden="true">↗</span></a></p>

    <div class="auth-form-note">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 5 6v5c0 4 3 7 7 10 4-3 7-6 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <p>Akses layanan peminjaman ruangan<br>untuk sivitas akademika FMIPA UNRI.</p>
    </div>
</x-auth-layout>
