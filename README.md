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

## Jadwal kuliah mingguan

Setelah memperbarui proyek, jalankan `php artisan migrate` dan `npm.cmd run build`.
Masuk sebagai admin, lalu buka **Jadwal kuliah mingguan** pada menu administrasi.
Isi semester, mata kuliah/kelas, ruangan aktif, dosen/penanggung jawab, jumlah peserta,
hari Senin–Jumat, jam, dan periode semester (maksimal 366 hari).

Sistem membuat semua pertemuan mendatang sekaligus. Jadwal langsung tampil pada
kalender dan memblokir peminjaman yang waktunya beririsan. Jika satu pertemuan
bentrok dengan jadwal disetujui, seluruh penyimpanan dibatalkan dan tanggal bentrok
ditampilkan. Pengajuan yang masih menunggu persetujuan diperiksa ulang saat disetujui.
Fitur ini tidak memerlukan cron atau scheduler; ketersediaan dihitung dari jadwal bertanggal.

Untuk hari libur atau kuliah pengganti, buka **Jadwal per tanggal**, lalu pilih
**Edit pertemuan ini** atau **Batalkan pertemuan ini**. Untuk mengubah pola,
gunakan **Ubah jadwal berikutnya** dan tentukan tanggal berlakunya. Riwayat,
pertemuan yang sudah dimulai, dan pengecualian individual dipertahankan.
**Hentikan rangkaian** membatalkan pertemuan yang belum dimulai sejak tanggal pilihan.

Peminjaman tetap diperbolehkan pada hari tanpa kuliah, termasuk Sabtu–Minggu,
dalam jam operasional 07:30–18:00 WIB. Peminjam wajib sudah memiliki surat izin
atau surat peminjaman ruangan dan tetap menunggu persetujuan admin.
Informasi ini tersedia pada halaman ruangan, form peminjaman, dan riwayat peminjaman;
admin juga diingatkan untuk memeriksa surat sebelum menyetujui pengajuan.

### Impor jadwal dari CSV

Buka **Jadwal kuliah mingguan → Impor CSV**, lalu unduh template.
Isi data melalui Excel dan simpan sebagai **CSV UTF-8 (.csv)**. Gunakan kode ruangan
dan NIP dosen/administrator yang sudah terdaftar; atur kolom NIP sebagai teks agar
angka nol di depan tetap tersimpan. Jam menggunakan `HH:MM` dalam WIB dan tanggal
menggunakan `YYYY-MM-DD`. Pemisah koma atau titik koma diterima.

Setiap baris berisi `semester`, `kode_ruangan`, `nip_dosen`, `mata_kuliah_kelas`,
`hari`, `mulai`, `selesai`, `peserta`, `awal_periode`, dan `akhir_periode`.
Maksimal 200 baris jadwal dan 1 MB per file.

Unggah CSV untuk melihat pratinjau baris siap impor, duplikat, dan kesalahan.
Perbaiki seluruh kesalahan di file dan unggah ulang sebelum menekan **Simpan hasil impor**.
Pratinjau berlaku selama 30 menit. Pemeriksaan mencakup ruangan aktif, NIP terdaftar,
kapasitas, jam operasional, periode, bentrok antarbaris, serta jadwal yang sudah disetujui.
Baris duplikat dalam file atau terhadap pola tersimpan dilewati tanpa mengubah jadwal,
termasuk rangkaian yang sudah dihentikan atau memiliki pengecualian pertemuan.

Penyimpanan memeriksa ulang keadaan database dan berjalan dalam satu transaksi.
Jika ada kesalahan atau bentrok baru, seluruh impor dibatalkan. Mengunggah ulang file
yang sama tidak menggandakan pola maupun pertemuan. Impor menambahkan pola baru;
perubahan pola tersimpan tetap dilakukan melalui **Ubah jadwal berikutnya**.

## Pemeriksaan

```powershell
composer test
npm.cmd run build
```

## Git

File `.env`, dependensi `vendor` dan `node_modules`, database SQLite lokal, log, serta aset hasil build diabaikan oleh Git. File `.env.example`, `composer.lock`, dan `package-lock.json` disimpan untuk memudahkan setup yang konsisten.

Dokumentasi framework: [Laravel 13](https://laravel.com/docs/13.x).
