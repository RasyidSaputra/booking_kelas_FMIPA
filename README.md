# Peminjaman Kelas FMIPA Universitas Riau

Aplikasi pengelolaan ruang kelas untuk **Fakultas Matematika dan Ilmu Pengetahuan Alam, Universitas Riau**. Pengguna dapat melihat ketersediaan ruangan, mengajukan peminjaman, dan memantau statusnya. Administrator mengelola data serta meninjau pengajuan.

**Web Blade dan REST API menggunakan database serta aturan peminjaman yang sama.**

[Instalasi](#instalasi) | [REST API](#rest-api) | [Struktur proyek](#struktur-proyek) | [Jadwal kuliah](#jadwal-kuliah-mingguan) | [Pengujian](#pengujian)

## Fitur

| Bagian | Kemampuan |
| --- | --- |
| Akun | Registrasi web, login NIM/NIP, profil; peran mahasiswa, dosen, dan admin |
| Ruangan | Pencarian ruangan aktif, kapasitas, fasilitas, dan informasi jadwal |
| Peminjaman | Pengajuan, riwayat pribadi, serta persetujuan atau penolakan admin |
| Administrasi web | Pengelolaan ruangan, fasilitas, pengguna, dan jadwal |
| Jadwal kuliah web | Pola mingguan per semester, pengecualian pertemuan, dan impor CSV dengan pratinjau |
| REST API v1 | Login token, profil akun, ruangan, pemeriksaan ketersediaan, pengajuan, riwayat, dan peninjauan admin |

API saat ini mencakup alur peminjaman utama. CRUD master data, registrasi, pengelolaan jadwal kuliah, serta impor CSV tersedia melalui web.

## Teknologi

- Laravel 13, PHP 8.4+, dan Composer 2.
- Laravel Sanctum 4.3 untuk Bearer token API.
- Blade, Alpine.js, Tailwind CSS, dan Vite.
- SQLite untuk instalasi awal; MySQL didukung melalui konfigurasi database.
- Zona waktu `Asia/Jakarta` (WIB), locale `id`.

## Instalasi

Prasyarat: PHP 8.4+, Composer 2, Node.js 22.12+ dan npm, serta ekstensi PHP yang dibutuhkan Laravel. Untuk SQLite, aktifkan PDO SQLite.

Jalankan dari direktori proyek di PowerShell **hanya untuk instalasi baru**:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database/database.sqlite -Force
php artisan migrate
npm.cmd install
npm.cmd run build
php artisan storage:link
```

Pada proyek yang sudah memiliki `.env` dan database, cukup perbarui dependensi dan jalankan migrasi:

```powershell
composer install
php artisan migrate
npm.cmd install
npm.cmd run build
```

Migrasi API menambahkan tabel `personal_access_tokens`. Gunakan migrasi biasa agar data dan akun yang sudah ada tetap tersimpan.

Untuk pengembangan:

```powershell
composer run dev
```

Buka **http://localhost:8000**. Perintah tersebut menjalankan server Laravel, queue worker, dan Vite. Untuk server saja dengan aset hasil build, gunakan `php artisan serve`.

Gunakan `npm.cmd` jika PowerShell memblokir `npm.ps1`. Script pengembangan tidak menjalankan Pail yang memerlukan PCNTL. Pada Laragon, arahkan document root ke folder `public` dan sesuaikan `APP_URL`.

### MySQL

Buat database `booking_kelas_fmipa`, kemudian atur `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_kelas_fmipa
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan `php artisan config:clear`, lalu `php artisan migrate`.

## REST API

Base URL lokal: **`http://localhost:8000/api/v1`**.

- [Panduan endpoint, autentikasi, dan contoh request](docs/API.md)
- [Koleksi Postman yang dapat diimpor](docs/postman/booking-kelas-fmipa.postman_collection.json)

Gunakan akun terdaftar yang sama dengan web. Login menggunakan `identity_number` (NIM/NIP), `password`, dan `device_name`. Registrasi pengguna baru dilakukan melalui web; akun admin dikelola sesuai mekanisme proyek.

| Method | Endpoint relatif | Akses |
| --- | --- | --- |
| POST | `/login` | Tanpa token |
| GET | `/me` | Pengguna login |
| POST | `/logout` | Pengguna login |
| GET | `/rooms` | Pengguna login |
| GET | `/rooms/{room}` | Pengguna login |
| GET | `/rooms/{room}/availability` | Pengguna login |
| GET | `/bookings` | Riwayat sendiri |
| POST | `/bookings` | Pengguna login |
| GET | `/bookings/{booking}` | Pemilik atau admin |
| GET | `/admin/bookings` | Admin |
| PATCH | `/admin/bookings/{booking}/review` | Admin |

Kirim `Accept: application/json` dan `Authorization: Bearer <token>` untuk endpoint terproteksi. Token berlaku **7 hari**; logout mencabut token yang digunakan saja. Login dibatasi 5 request/menit per kombinasi IP dan NIM/NIP serta 20 request/menit per IP. Endpoint terautentikasi dibatasi 120 request/menit per pengguna.

## Struktur proyek

```text
app/
  Http/Controllers/Api/     Controller JSON untuk API v1
  Http/Controllers/Admin/   Controller administrasi web
  Http/Requests/            Validasi input dan otorisasi request
  Http/Resources/           Data respons API yang ditampilkan secara eksplisit
  Services/                Aturan bisnis peminjaman, jadwal, dan impor
routes/
  web.php                  Halaman utama, peminjaman, dan profil web
  admin.php                Route administrasi web
  auth.php                 Autentikasi web
  api.php                  REST API /api/v1
  console.php              Perintah console
config/sanctum.php          Konfigurasi Bearer token API
```

`bootstrap/app.php` memuat route web dan API. `web.php` memuat `auth.php`, serta `admin.php` di dalam grup autentikasi web. Route admin tetap dilindungi hak `view-room-master`. Nama dan URL route web dipertahankan.

`BookingManagement` menangani pengajuan serta peninjauan untuk web dan API. Controller menentukan bentuk respons: halaman/redirect untuk web, JSON untuk API. Pemeriksaan ruangan aktif, kapasitas, dan bentrok dilakukan di dalam transaksi dengan penguncian ruangan.

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

## Pengujian

```powershell
php artisan test --compact
php artisan test --compact --filter=ApiTest
php artisan route:list --path=api --except-vendor
npm.cmd run build
```

Tes otomatis menggunakan **SQLite `:memory:`** dan memiliki penjagaan agar tidak menjalankan migrasi pada database utama. Cakupan API meliputi token, pembatasan login, otorisasi pemilik/admin, validasi waktu dan kapasitas, akhir pekan, bentrok, persetujuan, penolakan, dan kesesuaian aturan web/API.

Lihat [catatan verifikasi](docs/API.md#verifikasi-implementasi) untuk hasil pengujian dan batas pemeriksaannya.

## Git dan konfigurasi

`.env`, token/kata sandi asli, dependensi, database lokal, log, dan aset hasil build tidak disimpan di Git. Simpan `.env.example`, `composer.lock`, dan `package-lock.json` untuk instalasi yang konsisten.

Referensi resmi: [Laravel 13](https://laravel.com/docs/13.x), [Routing](https://laravel.com/docs/13.x/routing), dan [Sanctum](https://laravel.com/docs/13.x/sanctum).
