# Dashboard Peminjaman Kelas FMIPA Universitas Riau

Aplikasi berbasis web untuk membantu pengelolaan peminjaman ruang kelas di **Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA), Universitas Riau**. Dashboard ini ditujukan untuk mempermudah pengecekan ketersediaan kelas, pengajuan peminjaman, serta pemantauan penggunaan ruangan secara terpusat.

## Rencana fitur

- Dashboard ringkasan penggunaan dan peminjaman kelas.
- Pengelolaan data ruangan, kapasitas, dan fasilitas kelas.
- Informasi jadwal dan ketersediaan ruangan.
- Pengajuan peminjaman beserta persetujuan atau penolakannya oleh pengelola.
- Riwayat dan status peminjaman kelas.

Proyek saat ini berisi setup awal Laravel 13. Fitur peminjaman dan dashboard masih dalam tahap pengembangan; halaman awal masih menggunakan halaman bawaan Laravel.

## Teknologi

- Laravel 13 dan PHP 8.4 atau lebih baru.
- Blade, Vite, dan Tailwind CSS.
- SQLite untuk pengembangan awal; konfigurasi dapat disesuaikan ke MySQL.
- Zona waktu `Asia/Jakarta` dan locale aplikasi `id`.

## Menjalankan proyek

Prasyarat: PHP 8.4+, Composer 2, serta Node.js 22.12+ dengan npm. Pastikan ekstensi PHP yang dibutuhkan Laravel tersedia, termasuk PDO SQLite untuk konfigurasi bawaan.

Untuk instalasi pertama setelah clone, jalankan dari direktori proyek di PowerShell:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.mysql -Force
php artisan migrate
npm.cmd install
npm.cmd run build
```

Langkah pembuatan `.env` dan database di atas hanya untuk instalasi baru. Pada komputer yang sudah disiapkan, langsung jalankan:

```powershell
composer run dev
```

Buka **http://localhost:8000**. Perintah tersebut menjalankan server Laravel, queue worker, dan Vite. Untuk menjalankan server saja dengan aset hasil build, gunakan `php artisan serve`.

Di Windows, `npm.cmd` dapat digunakan jika PowerShell memblokir `npm.ps1`. Script `composer run dev` disesuaikan agar tidak menjalankan Laravel Pail yang membutuhkan ekstensi PCNTL.

Jika menggunakan virtual host Laragon, arahkan document root ke folder **public** dan sesuaikan `APP_URL` pada `.env`.

## Database MySQL (opsional)

Buat database `booking_kelas_fmipa`, lalu sesuaikan `.env` dengan kredensial MySQL lokal:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_kelas_fmipa
DB_USERNAME=root
DB_PASSWORD=
```

Kemudian jalankan `php artisan config:clear` dan `php artisan migrate`.

## Pemeriksaan

```powershell
composer test
npm.cmd run build
```

## Git

File `.env`, dependensi `vendor` dan `node_modules`, database SQLite lokal, log, serta aset hasil build diabaikan oleh Git. File `.env.example`, `composer.lock`, dan `package-lock.json` disimpan untuk memudahkan setup yang konsisten.

Dokumentasi framework: [Laravel 13](https://laravel.com/docs/13.x).
