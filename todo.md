# Persiapan dan TODO Laravel — Peminjaman Ruangan FMIPA UNRI

Dokumen ini berisi urutan persiapan dan checklist penerapan materi. Perintah contoh belum otomatis dianggap berhasil. Pengecualian yang sudah dijalankan sesuai permintaan Anda dicatat pada **Log eksekusi migration** di bawah; build dan feature test masih belum dijalankan oleh Codex.

## Navigasi

- [Log eksekusi migration](#log-eksekusi-migration)
- [A1 — Dashboard utama dan akses admin](#a1--dashboard-utama-dan-akses-admin)
- [A1 — Halaman login dan registrasi](#a1--halaman-login-dan-registrasi)
- [Perbaikan migration tabel](#perbaikan-migration-tabel)
- [Kondisi awal proyek](#kondisi-awal-proyek)
- [Keputusan paket dan stack](#keputusan-paket-dan-stack)
- [P0 — Persiapan lingkungan](#p0--persiapan-lingkungan)
- [P1 — Autentikasi Breeze Blade](#p1--autentikasi-breeze-blade)
- [P2 — Database dan penyimpanan](#p2--database-dan-penyimpanan)
- [P3 — Pembentukan kerangka modul](#p3--pembentukan-kerangka-modul)
- [P4 — Pemeriksaan dan cara menjalankan](#p4--pemeriksaan-dan-cara-menjalankan)
- [P5 — Urutan kerja dan bukti eksekusi](#p5--urutan-kerja-dan-bukti-eksekusi)
- [Checklist penerapan materi](#checklist-penerapan-materi-laravel)

## Log eksekusi migration

**26 Oktober 2026 — Codex, atas permintaan menjalankan migration.** Database MySQL lokal: `kelasku`.

Penyebab error `Unknown column 'is_active'`: tabel `rooms` masih hanya memiliki `id` dan timestamps, sementara lima migration penambahan field belum dijalankan. File migration yang dibutuhkan sudah tersedia, sehingga tidak perlu membuat migration duplikat atau mereset database.

Perintah yang benar-benar dijalankan:

```powershell
php artisan migrate:status
php artisan db:table rooms
php artisan db:table facilities
php artisan db:table bookings
php artisan db:table users
php artisan migrate --no-interaction
php artisan migrate:status
php artisan db:table rooms
php artisan db:table facility_room
php artisan db:table bookings
```

| Migration | Hasil |
| --- | --- |
| `2026_10_26_105131_add_details_to_rooms_table` | DONE / Ran / batch 3 |
| `2026_10_26_105132_add_name_to_facilities_table` | DONE / Ran / batch 3 |
| `2026_10_26_105133_fix_facility_room_table` | DONE / Ran / batch 3 |
| `2026_10_26_105134_add_details_to_bookings_table` | DONE / Ran / batch 3 |
| `2026_10_26_105135_ensure_identity_and_role_on_users_table` | DONE / Ran / batch 3 |

Hasil akhir: **13 migration berstatus Ran, tidak ada Pending.** Preflight migration lolos untuk tabel fitur awal yang kosong. Tidak menjalankan `migrate:fresh`, rollback, atau seeder.

| Tabel | Field yang kini tersedia selain id/timestamps |
| --- | --- |
| rooms | `code` unik, `name`, `capacity`, `location`, `description` nullable, `photo_path` nullable, `is_active` default true |
| facilities | `name` unik |
| facility_room | `room_id`, `facility_id`; foreign key dan unique pasangan; nama typo lama sudah dikoreksi |
| bookings | `user_id`, `room_id`, `purpose`, `participant_count`, `starts_at`, `ends_at`, `status` default pending, `reviewed_by` nullable, `reviewed_at` nullable, `rejection_reason` nullable; foreign key dan index jadwal |
| users | `identity_number` nullable/unik dan `role` default peminjam telah tersedia sebelum migration terbaru dan dipertahankan |

Verifikasi struktur melalui `db:table` mengonfirmasi `rooms.is_active` dan `rooms.name`, foreign key pivot, serta field/index/foreign key booking. Halaman dashboard dapat dicoba ulang setelah refresh; render dashboard di browser dan feature test belum diuji dalam eksekusi ini. Catatan belum dijalankan pada bagian lama di bawah merupakan riwayat tahap sebelumnya; gunakan log ini untuk status migration terbaru.

## A1 — Dashboard utama dan akses admin

Kode disiapkan pada **26 Oktober 2026**. Dashboard menampilkan navbar dengan logo Laravel/pencarian, sidebar responsif, tiga kartu aksi, kalender bulanan, dan jadwal pada tanggal terpilih. Data berasal dari database; tidak ada jadwal contoh atau indikator ramai yang dibuat secara acak.

| Halaman | Route | Perilaku |
| --- | --- | --- |
| Dashboard | `GET /dashboard` | Bookingan saya, Booking ruang, Cek ketersediaan; kalender default bulan sekarang |
| Daftar ruang | `GET /rooms` | Pencarian nama/kode/lokasi, filter tanggal, daftar ruang aktif dan jam booking disetujui |
| Bookingan saya | `GET /bookings` | Riwayat milik pengguna yang login, pagination, status dan alasan penolakan bila ada |
| Master data kelas | `GET /admin/rooms` | Daftar ruang aktif/nonaktif, pencarian dan pagination; khusus admin |

Mahasiswa dan dosen memakai akun `peminjam` pada skema role saat ini. Akun `admin` mendapat menu tambahan **Master data kelas**. Gate `view-room-master` didefinisikan di `app/Providers/AppServiceProvider.php`, dipakai melalui `@can` pada sidebar dan middleware `can:view-room-master` pada route. Menyembunyikan menu bukan satu-satunya pembatasan: request langsung oleh peminjam harus menghasilkan 403. Riwayat pribadi selalu difilter `user_id` pengguna login, termasuk bagi admin.

**Aturan kalender yang dipilih:** jumlah ruang aktif yang memiliki setidaknya satu booking `approved` bertumpang tindih dengan tanggal tersebut, dibagi jumlah seluruh ruang aktif. Ruangan dihitung sekali per tanggal. Booking `pending`, `rejected`, `cancelled`, serta ruang nonaktif tidak dihitung.

- Hijau: kurang dari 40% ruang memiliki jadwal disetujui pada hari itu.
- Kuning: 40% sampai kurang dari 80%.
- Merah: 80% atau lebih.
- Netral: belum ada ruang aktif, sehingga ketersediaan belum dapat dihitung.

Ini adalah indikator kepadatan berdasarkan **jumlah ruang**, bukan persentase jam pemakaian. Ruangan yang terpakai satu jam tetap tercatat terjadwal pada hari tersebut; lihat rincian waktu sebelum memilih ruangan. Rentang waktu memakai `starts_at < akhir_hari` dan `ends_at > awal_hari`, sehingga waktu selesai tepat tengah malam tidak ikut memenuhi hari berikutnya. Filter bulan/tahun mendukung tahun 2000–2100, termasuk Februari tahun kabisat. Kalender tidak mengungkap pemilik atau tujuan booking pengguna lain; hanya ruangan dan waktu pemakaian.

| File utama | Tanggung jawab |
| --- | --- |
| `resources/views/components/dashboard-layout.blade.php` | Navbar, logo Laravel, sidebar, pencarian, profil/logout, dan menu berdasarkan izin |
| `resources/views/dashboard.blade.php` | Kartu aksi, kalender, filter, legenda, dan rincian tanggal |
| `resources/css/dashboard.css` | Tampilan navy/teal, sidebar ponsel, kalender besar yang dapat digeser pada layar kecil |
| `app/Services/RoomCalendar.php` | Query booking disetujui, perhitungan kepadatan dan jadwal harian |
| `app/Http/Controllers/DashboardController.php` | Validasi filter, kalender, dan ringkasan booking pribadi |
| `RoomController::index` / `BookingController::index` | Daftar ruang yang dapat dicari dan riwayat milik sendiri |
| `app/Http/Controllers/Admin/RoomMasterController.php` | Pembacaan master data kelas khusus admin |
| `tests/Feature/DashboardTest.php` | Filter bulan/tahun, ambang warna, booking lintas hari, data kosong, pencarian, kepemilikan, dan hak akses admin |

**Batas tahap ini:** kartu Booking ruang membuka katalog dan jam pemakaian. Form pengajuan/penyimpanan booking adalah kelanjutan modul A3; halaman master saat ini menyediakan daftar data, sedangkan CRUD kelas diteruskan pada modul A2. Tidak ada tombol simpan atau hapus yang diarahkan ke controller kosong.

Tidak ada paket atau migration baru untuk dashboard. Pastikan migration fitur sebelumnya sudah selesai, lalu jalankan sendiri:

```powershell
php artisan config:clear
php artisan migrate:status
```

Untuk melihat tampilan, jalankan `php artisan serve` dan `npm.cmd run dev` di dua terminal terpisah, lalu login dan buka `/dashboard`. Alternatif aset statis: `npm.cmd run build`. Semua perintah aplikasi, build, dan feature test pada bagian ini belum dijalankan oleh Codex.

```powershell
php artisan test --filter=DashboardTest
```

Untuk mencoba admin secara manual pada lingkungan lokal, gunakan akun yang sudah terdaftar. Jalankan `php artisan tinker`, kemudian ganti email contoh berikut dengan akun uji Anda:

```php
$user = App\Models\User::where('email', 'email-admin-anda@contoh.ac.id')->firstOrFail();
$user->role = 'admin';
$user->save();
```

Registrasi publik tetap menghasilkan `peminjam`. Setelah itu buka dashboard memakai dua akun uji berbeda dan buktikan hasilnya:

- [ ] A1DB01 — Dashboard terbuka setelah login; navbar pencarian dan ketiga kartu menuju halaman/kalender yang sesuai.
- [ ] A1DB02 — Bulan awal sesuai tanggal aplikasi; filter bulan/tahun, sebelumnya/berikutnya, Hari ini, dan tanggal terpilih bekerja.
- [ ] A1DB03 — Warna sesuai data booking disetujui; data kosong tampil netral; rincian waktu tidak menganggap seluruh hari penuh.
- [ ] A1DB04 — Riwayat hanya menampilkan booking milik akun yang login; filter pencarian ruangan dan pagination bekerja.
- [ ] A1DB05 — Peminjam tidak melihat menu master dan mendapat 403 pada `/admin/rooms`; admin melihat menu dan daftar aktif/nonaktif.
- [ ] A1DB06 — Sidebar dapat dibuka/ditutup pada ponsel; kalender dapat digeser; tombol profil dan logout bekerja.
- [ ] A1DB07 — DashboardTest lolos; isi tanggal, commit dan hasil aktual sebagai bukti.

| Pemeriksaan | Hasil aktual | Tanggal/pelaksana |
| --- | --- | --- |
| Sintaks PHP/CSS | Lolos `php -l`, parsing PostCSS, dan `git diff --check`; belum membuktikan perilaku aplikasi | 26 Oktober 2026 / Codex |
| Feature test dashboard dan akses | Belum dijalankan | — |
| Build aset dan tampilan browser | Belum dijalankan | — |

## A1 — Halaman login dan registrasi

Pemilik: **Anda (A1)**. Kode awal disiapkan pada **26 Oktober 2026**, mengikuti referensi dashboard: latar abu muda, panel putih, warna navy/teal, dan sudut membulat. Status ini menunjukkan kode tersedia; build, test, dan pemeriksaan browser belum dijalankan oleh Codex.

| Bagian | File | Perilaku yang disiapkan |
| --- | --- | --- |
| Layout auth | `resources/views/components/auth-layout.blade.php` | Identitas Ruang FMIPA, navigasi masuk/daftar, ilustrasi denah, layout responsif |
| Field form | `resources/views/components/auth-field.blade.php` | Label, old input selain password, error per field, tombol tampil/sembunyikan kata sandi |
| Login | `resources/views/auth/login.blade.php` | Email, kata sandi, ingat saya, lupa kata sandi, tautan daftar |
| Registrasi | `resources/views/auth/register.blade.php` | Nama, NIM/NIP, email, kata sandi dan konfirmasi |
| Tampilan | `resources/css/auth.css`, diimpor oleh `resources/css/app.css` | Gaya auth dengan class khusus; tidak memerlukan paket baru |
| Validasi registrasi | `app/Http/Requests/Auth/RegisterRequest.php` | NIM/NIP wajib/unik, email unik, konfirmasi password, pesan form dalam bahasa Indonesia |
| Penyimpanan akun | `app/Http/Controllers/Auth/RegisteredUserController.php`, `app/Models/User.php` | Menyimpan input tervalidasi dan hash password; role ditetapkan server menjadi `peminjam` |
| Pesan login | `app/Http/Requests/Auth/LoginRequest.php`, `lang/id/auth.php` | Pesan input, kredensial salah, dan batas percobaan login |
| Pengujian | `tests/Feature/Auth/RegistrationTest.php` | Registrasi valid, hash password, role peminjam meski request mengirim admin, NIM/NIP wajib/unik, konfirmasi tidak cocok |

**Sebelum mencoba:** pastikan migration `2026_10_26_105135_ensure_identity_and_role_on_users_table` sudah berstatus `Ran`, karena form registrasi kini menyimpan `identity_number` dan `role`. Jika masih `Pending`, ikuti bagian perbaikan migration di bawah, lalu jalankan `php artisan migrate` ketika kondisi database sudah sesuai.

Untuk menjalankan secara lokal dari PowerShell:

```powershell
php artisan config:clear
php artisan migrate:status
```

Terminal pertama:

```powershell
php artisan serve
```

Terminal kedua:

```powershell
npm.cmd run dev
```

Buka `http://localhost:8000/login` dan `http://localhost:8000/register`. Jika memakai Laragon, gunakan domain lokal proyek dengan path yang sama. Alternatif aset statis: jalankan `npm.cmd run build`, lalu cukup jalankan server Laravel. Tidak diperlukan instalasi Breeze ulang atau paket tambahan.

Pengujian autentikasi yang dapat Anda jalankan sendiri; `phpunit.xml` memakai SQLite `:memory:` dan membutuhkan `pdo_sqlite`:

```powershell
php artisan test --filter='AuthenticationTest|RegistrationTest'
```

- [ ] A1UI01 — Login dan registrasi terbuka dengan style baru; navigasi antarhalaman bekerja.
- [ ] A1UI02 — Tampilan tidak melebar keluar layar pada ponsel; label, fokus keyboard, dan tombol tampil/sembunyikan kata sandi berfungsi.
- [ ] A1UI03 — Registrasi valid menyimpan NIM/NIP (termasuk angka nol di depan), login otomatis, dan menuju dashboard.
- [ ] A1UI04 — Email/NIM/NIP duplikat dan konfirmasi password yang salah ditolak dengan pesan per field; nilai password tidak ditampilkan kembali.
- [ ] A1UI05 — Login salah menampilkan pesan; login benar, ingat saya, dan logout tetap bekerja.
- [ ] A1UI06 — Seluruh skenario AuthenticationTest dan RegistrationTest lolos; simpan hasil aktual/commit sebagai bukti.

| Pemeriksaan | Hasil aktual | Tanggal/pelaksana |
| --- | --- | --- |
| Sintaks PHP pada controller/request/model/test/terjemahan | Lolos pemeriksaan `php -l` oleh Codex; ini belum membuktikan perilaku aplikasi | 26 Oktober 2026 / Codex |
| Sintaks CSS | Lolos parsing PostCSS; ini belum membuktikan hasil build atau tampilan browser | 26 Oktober 2026 / Codex |
| Build aset dan render Blade/browser | Belum dijalankan | — |
| Feature test autentikasi | Belum dijalankan | — |
| Uji alur login/registrasi pada desktop dan ponsel | Belum dijalankan | — |

Langkah A1 berikutnya: implementasi `EnsureUserIsAdmin`, perlindungan route admin, akun contoh di `UserSeeder`, serta pembaruan NIM/NIP pada profil. Halaman lupa/reset password masih menggunakan tampilan bawaan Breeze.

## Perbaikan migration tabel

Perbaikan kode pada **26 Oktober 2026**, berdasarkan log `Table 'rooms' already exists`. Perintah di bagian ini belum dijalankan oleh Codex.

Penyebab: migration awal sudah membuat tabel minimal `rooms`, `facilities`, `faciity_room` (salah ketik), dan `bookings`. File baru mencoba membuat ulang tabel yang sama. Mengedit migration yang sudah berstatus `Ran` juga tidak membuat Laravel menjalankannya lagi.

Perubahan yang disiapkan:

| File migration | Tindakan |
| --- | --- |
| `2026_10_26_101808_create_rooms_table.php` dan `2026_10_26_101829_create_facilities_table.php` | Dipertahankan sebagai pembuat tabel awal |
| `2026_10_26_101932_create_faciity_room_table.php` | Nama lama dipertahankan agar cocok dengan riwayat; koreksi dilakukan migration berikutnya |
| `2026_10_26_102203_add_identity_and_role_to_users_table.php` | Tetap menjadi placeholder riwayat; isi penambahan kolom dipindahkan ke migration baru agar database yang sudah mencatat versi kosong tetap diperbarui |
| `2026_10_26_102346_create_bookings_table.php` | Dikembalikan sesuai kerangka asli yang sebelumnya terhapus |
| `2026_10_26_105131_add_details_to_rooms_table.php` | Menambah kolom ruangan pada tabel yang sudah ada |
| `2026_10_26_105132_add_name_to_facilities_table.php` | Menambah nama fasilitas |
| `2026_10_26_105133_fix_facility_room_table.php` | Mengubah nama `faciity_room` menjadi `facility_room`, lalu menambah foreign key dan unique pasangan |
| `2026_10_26_105134_add_details_to_bookings_table.php` | Menambah data pengajuan, status, pemeriksa, foreign key, dan index jadwal |
| `2026_10_26_105135_ensure_identity_and_role_on_users_table.php` | Menambah identitas/role jika belum ada; identitas nullable/unik, role default peminjam |

File `105131`–`105134` yang sebelumnya bernama `create_*` diganti namanya karena sekarang mengubah tabel lama. Berdasarkan log, eksekusi berhenti pada pembuatan ulang `rooms`; belum ada verifikasi langsung terhadap tabel `migrations` di database Anda.

- [ ] MIG01 — Tinjau status migration dan kolom tabel sebelum menjalankan perubahan.

```powershell
php artisan migrate:status
```

Sintaks yang benar adalah `migrate:status`, bukan `migrate status`. Pada database yang mengalami error tersebut, migration awal diharapkan `Ran` dan migration `105131`–`105135` berstatus `Pending`. Jika versi lama `105131_create_rooms_table` atau file `create_*` baru lainnya justru sudah `Ran`, jangan lanjutkan sebelum riwayat dan skemanya diperiksa.

SQL berikut dapat dijalankan di klien MySQL pada database aplikasi untuk pemeriksaan tanpa mengubah data:

```sql
SHOW COLUMNS FROM rooms;
SHOW COLUMNS FROM facilities;
SHOW COLUMNS FROM faciity_room;
SHOW COLUMNS FROM bookings;
SHOW COLUMNS FROM users;
SELECT 'rooms' AS table_name, COUNT(*) AS total FROM rooms
UNION ALL SELECT 'facilities', COUNT(*) FROM facilities
UNION ALL SELECT 'faciity_room', COUNT(*) FROM faciity_room
UNION ALL SELECT 'bookings', COUNT(*) FROM bookings;
```

Empat tabel fitur awal diharapkan hanya memiliki `id`, `created_at`, `updated_at` dan belum berisi data. Migration penambahan kolom ruangan memeriksa keempat tabel tersebut terlebih dahulu dan berhenti jika ada data: kolom wajib seperti kode ruangan, pemilik pengajuan, serta tanggal tidak boleh diisi dengan tebakan. Jika sudah berisi data, diperlukan backfill bertahap sesuai data sebenarnya. Akun pada `users` tetap dapat dipertahankan.

- [ ] MIG02 — Setelah kondisi awal sesuai, jalankan migration biasa. Tidak perlu `migrate:fresh` atau menghapus tabel.

```powershell
php artisan migrate
php artisan migrate:status
```

- [ ] MIG03 — Periksa `facility_room` sudah memakai ejaan benar; kolom, foreign key, nilai default, dan index baru tersedia. Catat hasil aktual pada tabel berikut.

| Pemeriksaan | Hasil aktual | Tanggal/pelaksana |
| --- | --- | --- |
| Status migration sebelum perbaikan | Belum dijalankan | — |
| Skema awal dan jumlah data tabel fitur | Belum dijalankan | — |
| `php artisan migrate` | Belum dijalankan | — |
| Status akhir dan skema hasil | Belum dijalankan | — |

Setiap migration perubahan memiliki `down()` untuk membalik perubahan kolom/relasi; rollback menghapus data pada kolom yang diturunkan. Gunakan rollback hanya pada database uji yang aman. Rangkaian ini memperbaiki struktur tabel, belum mengimplementasikan relasi model, validasi jadwal, atau policy fitur.

## Kondisi awal proyek

Audit file pada **26 Oktober 2026**; status berikut bukan hasil menjalankan aplikasi.

| Bagian | Temuan dari repository | Tindak lanjut |
| --- | --- | --- |
| Backend | `composer.json`: PHP `^8.4`, Laravel `^13.0`; `composer.lock`: Laravel `v13.38.0` | Cocokkan PHP CLI dan dependency aktual melalui P0 |
| Dependensi lokal | Folder `vendor` dan `node_modules` serta kedua lockfile sudah ada | Keberadaan folder belum membuktikan dependensi sesuai lockfile |
| Frontend | Blade, Vite 7, Tailwind 4, `@tailwindcss/vite` | Sesuaikan ke hasil scaffold Breeze Blade pada P1 |
| Autentikasi | Breeze belum tercantum; belum ada `routes/auth.php` | Pasang Breeze Blade dengan PHPUnit |
| Route dan view | `/` masih menampilkan `welcome`; controller fitur belum ada | Mulai fitur setelah autentikasi lolos pemeriksaan |
| Database | Migration users/password reset/sessions, cache, jobs tersedia | Jangan membuat ulang tabel bawaan tersebut |
| Model | `User` tersedia dengan cast password `hashed`; belum ada Room/Facility/Booking | Tambahkan model dan relasi pada P3 |
| Konfigurasi contoh | SQLite; session/cache/queue memakai database; mail memakai log | Pilih database lokal dan jalankan migration |
| Pengujian | PHPUnit 12; `phpunit.xml` memakai SQLite `:memory:` | Pertahankan database uji terpisah; butuh `pdo_sqlite` |
| Data contoh | `DatabaseSeeder` baru membuat satu Test User | Isi seeder fitur dan akun admin/peminjam kemudian |

## Keputusan paket dan stack

Rencana persiapan: **Laravel 13 + Breeze Blade + Alpine + PHPUnit**, mengikuti materi autentikasi dan latihan Blade. Role admin/peminjam memakai kolom role, middleware, dan policy Laravel. CRUD, validasi, pagination, factory, transaksi, serta upload sudah tersedia di Laravel.

| Kebutuhan | Persiapan | Keterangan |
| --- | --- | --- |
| Login, registrasi, logout, profil | Tambahkan `laravel/breeze` sebagai dependency pengembangan | Scaffold kemudian menjadi kode aplikasi di repository |
| Tampilan autentikasi | Gunakan hasil Breeze Blade dengan Tailwind 3 | Perubahan dari Tailwind 4 disengaja agar mengikuti scaffold; jangan mencampur konfigurasi kedua versi |
| Pengujian dan format PHP | PHPUnit dan Pint sudah tercantum | Tidak perlu memasang Pest atau formatter lain |
| MySQL | Driver `pdo_mysql`, layanan MySQL/MariaDB Laragon | Disarankan untuk integrasi persetujuan bersamaan dengan penguncian baris |
| Upload foto | Disk `public` dan `storage:link` | Tidak memerlukan paket upload tambahan |
| Laporan | Mulai dengan tabel/rekap dan CSV bila diperlukan | Paket PDF/Excel hanya dipilih jika format tersebut disepakati |
| API, permission kompleks, kalender | Belum perlu paket tambahan | Sanctum, Spatie Permission, FullCalendar, Redis, dan Livewire bukan prasyarat checklist ini |

Pemeriksaan sumber resmi: branch Breeze `2.x` menyatakan dukungan Illuminate `^13.0`, tetapi installer Blade masih menghasilkan Tailwind 3 dan menjalankan npm install/build sendiri. Ini belum membuktikan versi stabil yang akan dipilih Composer cocok di komputer Anda; lakukan dry run pada P1. Dokumentasi starter kit Laravel 13 saat ini membahas starter kit berbasis Fortify; alur di sini khusus Breeze yang ditambahkan ke proyek yang sudah ada.

Referensi: [dependensi Breeze](https://github.com/laravel/breeze/blob/2.x/composer.json), [installer Blade dan perubahan aset](https://github.com/laravel/breeze/blob/2.x/src/Console/InstallsBladeStack.php), [opsi installer dan PHPUnit](https://github.com/laravel/breeze/blob/2.x/src/Console/InstallCommand.php), [starter kit Laravel 13](https://laravel.com/docs/13.x/starter-kits).

## P0 — Persiapan lingkungan

Jalankan blok secara bertahap dari **PowerShell** di root proyek. Baca catatan sebelum menjalankan; berhenti jika ada error dan catat hasilnya pada P5. Jangan menyalin seluruh dokumen sebagai satu script.

- [x] SET01 — Pastikan pekerjaan lokal tersimpan sebelum scaffold karena Breeze dapat menimpa route, view, dan aset.
- [x] SET02 — PHP CLI minimal 8.4, Composer 2, Node.js 22.12+ dan npm tersedia. PHP CLI harus memakai versi yang sesuai dengan Laragon.

```powershell
Set-Location 'E:\laragon\www\booking_kelas_FMIPA'
git status --short
Get-Command php, composer, node, npm.cmd
php --version
php --ini
php -m
composer --version
node --version
npm.cmd --version
```

Pada hasil `php -m`, periksa ekstensi Laravel, termasuk PDO, mbstring, OpenSSL, fileinfo, XML/DOM, serta `pdo_sqlite` untuk test. Aktifkan `pdo_mysql` jika memilih MySQL. Jika perlu mengubah ekstensi, edit `php.ini` yang ditunjukkan PHP CLI lalu periksa ulang.

- [x] SET03 — Sinkronkan dependency PHP dengan lockfile dan periksa persyaratan platform.

```powershell
composer install
composer check-platform-reqs
```

Jangan menjalankan `composer update` menyeluruh untuk setup awal. `composer install` memakai lockfile yang sudah ada dan dapat menjalankan script package discovery Laravel.

- [x] SET04 — Siapkan `.env` dan APP_KEY hanya bila belum tersedia.

```powershell
if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
}
```

Periksa `.env` di editor. Jalankan perintah berikut **hanya jika APP_KEY masih kosong**, agar key proyek yang sudah dipakai tidak diganti:

```powershell
php artisan key:generate
```

Pastikan nilai berikut ada pada `.env`; `.env.example` sudah menyediakan nilai ini. Jangan menyalin APP_KEY atau password lokal ke dokumentasi.

```dotenv
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_URL=http://localhost:8000
MAIL_MAILER=log
```

## P1 — Autentikasi Breeze Blade

- [x] SET05 — Periksa resolusi paket sebelum instalasi.

```powershell
composer show laravel/breeze --all
composer require laravel/breeze --dev --dry-run
```

Pastikan versi yang ditawarkan adalah rilis stabil dengan dukungan Laravel 13, lalu catat versinya pada P5. Proyek memiliki `minimum-stability: dev`; jangan menerima branch dev secara tidak sengaja. Jika Composer melaporkan konflik, simpan pesan error dan periksa constraint paket. Jangan memakai `--ignore-platform-reqs` atau menurunkan Laravel untuk memaksakan instalasi.

- [x] SET06 — Pasang Breeze setelah dry run sesuai.

```powershell
composer require laravel/breeze --dev
composer show laravel/breeze
```

- [x] SET07 — Hapus deklarasi plugin Tailwind 4 yang tidak digunakan scaffold Breeze, lalu buat autentikasi Blade.

```powershell
npm.cmd pkg delete 'devDependencies.@tailwindcss/vite' (saya skip)
php artisan breeze:install blade
```

Gunakan perintah eksplisit `blade` di atas **tanpa `--pest`**, sehingga pengujian memakai PHPUnit yang sudah ada. Installer dapat menjalankan `npm install` dan `npm run build` secara otomatis. Jika memakai installer interaktif, pilih Blade dan PHPUnit.

Alasan langkah plugin: scaffold Breeze yang diperiksa mengganti `tailwindcss` ke versi 3 dan menimpa `vite.config.js`, `resources/css/app.css`, serta `resources/js/app.js`. Plugin `@tailwindcss/vite` versi 4 bawaan proyek tidak diperlukan pada konfigurasi tersebut. Jangan membangun aset di sela penghapusan deklarasi plugin dan pemasangan scaffold, karena konfigurasi Vite lama masih merujuk plugin itu.

- [x] SET08 — Tinjau perubahan dan pastikan dependency/aset konsisten, termasuk jika npm otomatis dari installer gagal.

```powershell
git diff --stat
git diff -- composer.json package.json vite.config.js resources/css/app.css resources/js/app.js routes/web.php
npm.cmd install
npm.cmd ls tailwindcss alpinejs
npm.cmd run build
```

Hasil yang perlu diperiksa: `routes/auth.php`, controller `Auth`, request login, `ProfileController`, layout/komponen Blade, halaman auth/profil/dashboard, serta test auth. Konfigurasi hasil scaffold harus memakai Tailwind 3, `tailwind.config.js` dan `postcss.config.js`, tanpa import `@tailwindcss/vite`. Halaman `welcome` lama mungkin memakai gaya Tailwind 4; ganti atau sesuaikan saat membuat halaman aplikasi.

Jangan jalankan `breeze:install` ulang setelah fitur dimodifikasi tanpa meninjau dampaknya. Simpan `composer.lock` dan `package-lock.json` yang dihasilkan agar anggota lain memakai versi sama. Anggota yang clone setelah scaffold masuk repository cukup menjalankan `composer install` dan `npm.cmd ci`, tanpa memasang scaffold lagi.

## P2 — Database dan penyimpanan

- [x] SET09 — Konfigurasi database pengembangan integrasi: MySQL; 

**Pilihan A: MySQL/MariaDB Laragon.** Nyalakan layanan database, lalu jalankan SQL berikut di HeidiSQL/phpMyAdmin atau klien SQL untuk membuat database lokal jika belum ada:

```sql
CREATE DATABASE IF NOT EXISTS kelasku
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Edit `.env` dengan kredensial lokal; contoh berikut bukan jaminan password server Anda kosong:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kelasku
DB_USERNAME=root
DB_PASSWORD=
```

Gunakan tabel InnoDB untuk rancangan transaksi/penguncian persetujuan. SQLite test bawaan tidak membuktikan perilaku penguncian MySQL; uji dua persetujuan bersamaan pada database MySQL uji terpisah saat mengerjakan PJ05.

File kosong `database/database.mysql` yang disebut README lama bukan cara membuat database MySQL dan tidak diperlukan.  untuk MySQL, gunakan SQL pembuatan database di atas.

- [x] SET10 — Terapkan konfigurasi dan migration bawaan setelah koneksi siap.

```powershell
php artisan config:clear
php artisan migrate
php artisan migrate:status
```

Migration saat ini baru menyiapkan tabel bawaan. Migration fitur dijalankan lagi setelah P3 selesai diisi. Tabel sessions/cache/jobs sudah disediakan, sehingga tidak perlu menjalankan generator tabel tersebut lagi. Jangan memakai `migrate:fresh` pada database yang berisi pekerjaan/data yang ingin dipertahankan.

- [x] SET11 — Siapkan akses foto publik.

```powershell
php artisan storage:link
```

Saat implementasi, simpan foto melalui disk `public` secara eksplisit, misalnya `store('rooms', 'public')`. Disk default `.env.example` masih `local`. Tautan publik saja belum menyediakan fitur upload atau validasi. Jika Windows menolak symlink, periksa izin Developer Mode/symlink lalu ulangi langkah ini. Jika tautan sudah ada dan valid, tidak perlu membuatnya ulang.

## P3 — Pembentukan kerangka modul

Perintah berikut **opsional sampai tim mulai modul terkait**. Jalankan sekali untuk file yang belum ada; generator hanya membentuk kerangka, belum mengimplementasikan fitur. Sepakati nama kolom dan relasi sebelum migration dijalankan. `User`, `UserFactory`, dan `DatabaseSeeder` sudah ada, sehingga cukup diedit.

- [x] SET12 — A2 membuat model, migration, factory ruangan/fasilitas dan pivot.

```powershell
php artisan make:model Room -mf
php artisan make:model Facility -mf
php artisan make:migration create_facility_room_table
php artisan make:controller RoomController --model=Room --resource
php artisan make:controller FacilityController --model=Facility --resource
php artisan make:request StoreRoomRequest
php artisan make:request UpdateRoomRequest
php artisan make:request StoreFacilityRequest
php artisan make:request UpdateFacilityRequest
php artisan make:policy RoomPolicy --model=Room
php artisan make:policy FacilityPolicy --model=Facility
php artisan make:seeder FacilitySeeder
php artisan make:seeder RoomSeeder
```

- [x] SET13 — A1 menyiapkan tambahan profil/role dan pembatasan admin.

```powershell
php artisan make:migration add_identity_and_role_to_users_table --table=users
php artisan make:middleware EnsureUserIsAdmin
php artisan make:seeder UserSeeder
```

Breeze menangani autentikasi dasar. Tambahkan NIM/NIP dan role secara manual; pendaftaran publik harus selalu menghasilkan peminjam. Daftarkan alias middleware di `bootstrap/app.php`, lalu terapkan middleware dan policy pada route/controller. Layout Breeze memakai komponen Blade; tetap buat penerapan `@extends/@section/@yield` yang relevan untuk BL07 bila mengikuti checklist tersebut.

- [x] SET14 — A3/A4 menyiapkan pengajuan, pemeriksaan, jadwal, dan laporan.

```powershell
php artisan make:model Booking -mf
php artisan make:controller BookingController --model=Booking
php artisan make:controller Admin/BookingReviewController
php artisan make:controller DashboardController --invokable
php artisan make:controller ScheduleController --invokable
php artisan make:controller ReportController
php artisan make:request StoreBookingRequest
php artisan make:request ReviewBookingRequest
php artisan make:policy BookingPolicy --model=Booking
php artisan make:seeder BookingSeeder
```

Rancangan minimum untuk dibahas sebelum mengisi file:

| Tabel | Kolom/relasi yang direncanakan |
| --- | --- |
| users | identitas NIM/NIP dan role admin/peminjam, selain kolom bawaan |
| rooms | nama/kode unik, kapasitas, lokasi, deskripsi, path foto, status aktif |
| facilities | nama fasilitas dan atribut yang memang dipakai |
| facility_room | room_id, facility_id, unique pasangan; timestamps jika relasi menggunakan `withTimestamps()` |
| bookings | user_id, room_id, tujuan, jumlah peserta, starts_at, ends_at, status, reviewed_by nullable, reviewed_at nullable, alasan penolakan nullable |

Isi migration, fillable/casts, relasi, Form Request, method controller, policy, middleware, route dan view sebelum menguji fitur. Sepakati nilai status yang konsisten, misalnya `pending`, `approved`, `rejected`, `cancelled`. Pertahankan riwayat dengan menonaktifkan ruangan dan kebijakan foreign key yang sesuai. Untuk PJ05, pertimbangkan transaksi yang mengunci baris ruangan, lalu mengecek ulang bentrok dan status sebelum menyimpan persetujuan; semua jalur persetujuan harus mengikuti aturan yang sama.

- [ ] SET15 — Isi seeder/factory, panggil seeder dari `DatabaseSeeder`, lalu jalankan setelah implementasi datanya siap.

Urutan seeder: pengguna → fasilitas → ruangan/pivot → peminjaman. Sesuaikan seeder Test User bawaan agar tidak membuat email duplikat ketika seeding diulang. Akun contoh hanya untuk lingkungan lokal.

```powershell
php artisan migrate
php artisan db:seed
```

## P4 — Pemeriksaan dan cara menjalankan

- [x] SET16 — Setelah Breeze terpasang, periksa route, test yang tersedia, dan hasil build.

```powershell
php artisan route:list
composer test
npm.cmd run build
```

Periksa adanya route login, register, logout, profil, dan dashboard; periksa tidak ada tabrakan nama route. `composer test` memakai konfigurasi `phpunit.xml` yang sudah menargetkan SQLite `:memory:`. Jangan menimpa konfigurasi test dengan database pengembangan.

- [x] SET17 — Jalankan aplikasi melalui dua terminal PowerShell.

Terminal 1:

```powershell
php artisan serve
```

Terminal 2:

```powershell
npm.cmd run dev
```

Buka `http://localhost:8000`. Alternatif satu terminal: `composer run dev` yang sudah tersedia menjalankan server, queue listener, dan Vite. Jika memakai virtual host Laragon, arahkan document root ke `public` dan sesuaikan `APP_URL`. Hentikan proses pengembangan dengan Ctrl+C.

- [x] SET18 — Coba registrasi → login → dashboard → edit profil → logout; guest harus diarahkan ke login saat mengakses dashboard. Reset password memakai `MAIL_MAILER=log` untuk pengembangan lokal; jangan melampirkan tautan/token reset ke dokumentasi publik. Alur admin/peminjam baru bisa dibuktikan setelah role/policy dibuat.

Saat mulai pengujian fitur, generator berikut menyiapkan file test; lengkapi skenario sesuai TS01–TS08, bukan hanya test response bawaan:

```powershell
php artisan make:test RoomTest
php artisan make:test BookingTest
php artisan make:test BookingAuthorizationTest
php artisan make:test BookingApprovalTest
```

## P5 — Urutan kerja dan bukti eksekusi

1. Semua anggota menyelesaikan P0; satu anggota A1 memasang dan mereview Breeze melalui P1.
2. Pilih database bersama, jalankan P2, lalu buktikan autentikasi melalui P4.
3. Sepakati skema P3; kerjakan ruangan/fasilitas (A2), lalu pengajuan (A3), kemudian persetujuan/jadwal/laporan (A4). A1 menyelesaikan role dan akses bersama anggota modul.
4. Setelah scaffold disimpan ke repository, anggota lain memakai lockfile yang sama. Buat migration baru untuk perubahan skema; jangan memasang ulang Breeze pada tiap modul.
5. Lengkapi pengujian, bukti checklist A–D, README, dan demo. Pembentukan file saja belum boleh mencentang fitur selesai.

| Tahap/ID | Perintah atau pemeriksaan | Versi paket/commit | Hasil aktual atau lokasi bukti | Tanggal/pelaksana |
| --- | --- | --- | --- | --- |
| SET01–SET04 | Lingkungan, composer install, .env/key | Belum dicatat | Belum dijalankan dalam pekerjaan dokumentasi ini | — |
| SET05–SET08 | Dry run, instalasi Breeze, dependency dan build | Breeze: … / Tailwind: … | Belum dijalankan | — |
| SET09–SET11 | Koneksi DB, migrate:status, storage link | DB/versi: … | Belum dijalankan | — |
| SET12–SET15 | Generator dan implementasi awal tiap modul | … | Belum dijalankan | — |
| SET16–SET18 | Route, test, build, pemeriksaan browser | … | Belum dijalankan | — |

Isi hasil nyata, termasuk error dan langkah perbaikan. Dokumentasi perintah adalah bukti rencana/prosedur; bukti keberhasilan memerlukan hasil eksekusi Anda. Cukup simpan ringkasan output, screenshot yang relevan, atau tautan commit/PR; jangan menyertakan `.env`, kredensial, cookie, maupun token.

---

## Checklist Penerapan Materi Laravel

Proyek: Dashboard Peminjaman Ruangan FMIPA UNRI
Tim: 4 anggota
Target proyek: Laravel 13 dan PHP 8.4
Dasar checklist: 9 PDF materi dan latihan yang diberikan dosen.

### Cara menggunakan

- Ubah - [ ] menjadi - [x] setelah materi diterapkan, dicoba, dan hasilnya sesuai.
- Isi bukti pada tabel pelacakan: path file aktual, nama method atau route, dan commit/PR bila tersedia.
- Status checklist materi dipertahankan belum dicentang. Audit persiapan di atas hanya memeriksa file; belum ada verifikasi perilaku aplikasi atau hasil test.
- Bagian A adalah target penerapan yang disarankan untuk proyek, bukan daftar kewajiban baru dari dosen.
- Bagian B memuat materi pilihan. Tidak semua contoh, sintaks alternatif, atau fitur pada slide harus dipaksakan masuk aplikasi.
- Satu implementasi boleh membuktikan beberapa materi. Contoh: form tambah ruangan mencakup route, controller, request, validasi, Blade, dan Eloquent.
- Nama file dan method di bawah merupakan usulan; sesuaikan dengan struktur proyek yang sebenarnya.

### Pembagian pemilik modul

| Kode | Anggota | Modul |
| --- | --- | --- |
| A1 | Anda (nama: …) | Autentikasi, profil, hak akses, layout bersama |
| A2 | Nama: … | Ruangan, fasilitas, pencarian |
| A3 | Nama: … | Pengajuan dan riwayat peminjaman |
| A4 | Nama: … | Persetujuan, jadwal, dashboard, laporan |

### Daftar sumber

Nomor halaman mengacu pada urutan halaman PDF, termasuk halaman judul.

| Kode | File materi | Cakupan |
| --- | --- | --- |
| S1 | laravel-route-controller.pdf | Routing, controller, binding, middleware, resource, testing |
| S2 | laravel-blade-templating.pdf | View, direktif, layout, komponen, form, aset, testing |
| S3 | laravel-blade-templating-exercise.pdf | Latihan controller ke view, loop, layout, komponen |
| S4 | laravel-request-response-revised.pdf | Input, validasi, unggahan, redirect, JSON, response, testing |
| S5 | laravel-request-response-exercise.pdf | Latihan profil, validasi, old input, flash message, Form Request |
| S6 | laravel-database-eloquent.pdf | Query Builder, migration, Eloquent, relasi, collection |
| S7 | practice-database-eloquent.pdf | Latihan database, factory, scope, relasi, collection |
| S8 | laravel-crud.pdf | CRUD lengkap, validasi, policy, pencarian, testing, pola opsional |
| S9 | laravel-auth-breeze.pdf | Autentikasi, Breeze, middleware, session, keamanan, testing |

## A — Target penerapan pada fitur utama

### 1 Routing dan controller

Pemilik: Semua anggota sesuai modul.
Sumber: S1 hlm. 4–5, 7–20, 23, 28, 32, 35, 37–39; S8 hlm. 12–20.

- [ ] RT01 — Mendefinisikan route web di routes/web.php dan memahami konfigurasinya di bootstrap/app.php.
- [ ] RT02 — Menggunakan GET untuk daftar/detail/form, POST untuk menyimpan, PUT/PATCH untuk memperbarui, dan DELETE untuk penghapusan yang diizinkan.
- [ ] RT03 — Menghubungkan route dengan controller; logika fitur tidak menumpuk di file route.
- [ ] RT04 — Menggunakan parameter route untuk detail ruangan dan peminjaman.
- [ ] RT05 — Menggunakan named route dan helper route() pada tautan serta form.
- [ ] RT06 — Mengelompokkan route admin dengan prefix dan middleware yang sesuai.
- [ ] RT07 — Menggunakan resource controller/route untuk CRUD ruangan atau fasilitas; batasi action yang tidak diperlukan.
- [ ] RT08 — Menggunakan implicit route model binding, misalnya show(Room $room); ID yang tidak ditemukan menghasilkan 404.
- [ ] RT09 — Menggunakan dependency injection Request atau Form Request pada controller.
- [ ] RT10 — Memeriksa route dengan php artisan route:list dan memastikan tidak ada nama/path yang bertabrakan.

Bukti yang dicari: route aktual, controller terkait, serta hasil akses daftar, detail, dan ID yang tidak ada.

### 2 Blade dan tampilan

Pemilik: A1 untuk layout, semua anggota untuk view modul.
Sumber: S2 hlm. 4–7, 10–19, 22–29, 31–39, 47; S3 hlm. 3–8; S8 hlm. 24–30.

- [ ] BL01 — Mengirim data dari controller ke file .blade.php dengan view().
- [ ] BL02 — Menampilkan input pengguna dengan {{ }} agar output di-escape.
- [ ] BL03 — Menggunakan @if/@else untuk kondisi status atau ketersediaan tombol.
- [ ] BL04 — Menggunakan @foreach untuk daftar ruangan, fasilitas, atau pengajuan.
- [ ] BL05 — Menggunakan @forelse untuk pesan ketika belum ada data.
- [ ] BL06 — Menggunakan $loop, misalnya nomor urut pada tabel.
- [ ] BL07 — Membuat layout bersama dengan @extends, @section, dan @yield.
- [ ] BL08 — Memisahkan navigasi/sidebar/pesan menjadi partial melalui @include.
- [ ] BL09 — Membuat komponen reusable, misalnya <x-room-card> atau <x-status-badge>, dengan data props dan/atau slot.
- [ ] BL10 — Menambahkan @csrf pada form yang mengubah data.
- [ ] BL11 — Menggunakan @method('PUT'), PATCH, atau DELETE sesuai method route.
- [ ] BL12 — Menampilkan old() dan @error agar form mudah diperbaiki ketika validasi gagal.
- [ ] BL13 — Menggunakan @auth/@guest untuk tampilan navigasi sesuai status login.
- [ ] BL14 — Memuat aset melalui @vite dan memastikan tampilan dapat dijalankan setelah instalasi.
- [ ] BL15 — Menampilkan pagination dengan links() pada daftar yang dipaginasi.

Bukti yang dicari: layout, komponen, view modul, kondisi data kosong, dan form yang gagal validasi.

### 3 Request validasi dan response

Pemilik: Semua anggota; A2 unggahan, A3 validasi pengajuan.
Sumber: S4 hlm. 5, 12–17, 20–25, 28–39, 41–44, 48–51; S5 hlm. 3–8; S8 hlm. 21–23, 33.

- [ ] RQ01 — Membaca input form melalui Request dan query pencarian melalui query() atau input().
- [ ] RQ02 — Menangani filter opsional dengan pengecekan input seperti filled()/has() sesuai kebutuhan.
- [ ] RQ03 — Memvalidasi field wajib, tipe data, email, angka, panjang teks, dan nilai yang diperbolehkan sesuai form.
- [ ] RQ04 — Memvalidasi referensi ruangan/fasilitas yang dipilih dengan aturan seperti exists.
- [ ] RQ05 — Memvalidasi waktu selesai setelah waktu mulai dan jumlah peserta tidak melebihi kapasitas; logika kapasitas disesuaikan dengan ruangan terpilih.
- [ ] RQ06 — Memisahkan validasi form yang cukup besar ke Form Request, misalnya StoreBookingRequest dan request ruangan.
- [ ] RQ07 — Menyimpan hanya data tervalidasi; tidak meneruskan seluruh $request->all() ke model.
- [ ] RQ08 — Memberikan pesan validasi yang jelas untuk pengguna.
- [ ] RQ09 — Mengunggah foto ruangan dengan validasi gambar/tipe dan ukuran berkas serta penyimpanan melalui disk yang sesuai.
- [ ] RQ10 — Menggunakan redirect setelah simpan/ubah/batal agar refresh tidak mengirim ulang form.
- [ ] RQ11 — Menampilkan flash message sukses/gagal melalui session.
- [ ] RQ12 — Menggunakan response yang sesuai: view untuk halaman, redirect setelah aksi, 403 untuk akses terlarang, dan 404 untuk data tidak ditemukan.

Bukti yang dicari: Request class, method store/update, konfigurasi akses foto, dan hasil input valid/tidak valid.

### 4 Migration dan data contoh

Pemilik: A1 tabel users; A2 rooms/facilities/pivot; A3 bookings dengan kesepakatan A4.
Sumber: S6 hlm. 4, 15–21; S7 hlm. 3–5; S8 hlm. 5–11.

- [ ] DB01 — Menyiapkan koneksi database melalui .env dan contoh konfigurasi tanpa rahasia pada .env.example.
- [ ] DB02 — Membuat migration untuk users, rooms, facilities, facility_room, dan bookings sesuai desain final.
- [ ] DB03 — Menyediakan up() dan down() yang sesuai pada migration.
- [ ] DB04 — Memilih tipe kolom yang sesuai, termasuk waktu, kapasitas, status, dan timestamps.
- [ ] DB05 — Menetapkan foreign key dan kebijakan penghapusan agar riwayat peminjaman tidak hilang tanpa sengaja.
- [ ] DB06 — Menambahkan unique constraint yang diperlukan, termasuk email dan pasangan room_id–facility_id pada pivot.
- [ ] DB07 — Menetapkan default/nullable dan index sesuai kebutuhan pencarian serta relasi.
- [ ] DB08 — Memastikan migration berjalan pada database pengembangan yang kosong; mencoba rollback hanya pada database uji yang aman.
- [ ] DB09 — Membuat seeder untuk akun admin/peminjam, fasilitas, ruangan, dan contoh pengajuan berbagai status.
- [ ] DB10 — Membuat factory untuk menghasilkan data pengujian yang masuk akal.

Bukti yang dicari: migration, seeder/factory, dan langkah setup database di README.

### 5 Eloquent dan relasi

Pemilik: Semua anggota sesuai model.
Sumber: S6 hlm. 22–31, 34, 36; S7 hlm. 8–11; S8 hlm. 9, 14–20, 29.

- [ ] EL01 — Membuat dan menggunakan model User, Room, Facility, dan Booking.
- [ ] EL02 — Mengatur $fillable/$guarded agar mass assignment terkendali; role dan status persetujuan tidak dapat diubah sembarang pengguna.
- [ ] EL03 — Menerapkan casting waktu mulai/selesai menjadi datetime serta atribut boolean/integer yang relevan.
- [ ] EL04 — Menggunakan operasi Eloquent untuk create, read, update, dan delete yang diizinkan.
- [ ] EL05 — Membuat relasi User hasMany Booking dan Booking belongsTo User.
- [ ] EL06 — Membuat relasi Room hasMany Booking dan Booking belongsTo Room.
- [ ] EL07 — Membuat relasi pemeriksa Booking belongsTo User melalui reviewed_by.
- [ ] EL08 — Membuat relasi many-to-many Room–Facility melalui tabel pivot.
- [ ] EL09 — Menyimpan pilihan fasilitas dengan sync() atau operasi pivot yang sesuai.
- [ ] EL10 — Membuat local scope yang relevan, misalnya ruangan aktif atau pengajuan menunggu.
- [ ] EL11 — Menggunakan eager loading with() pada daftar pengajuan yang menampilkan peminjam dan ruangan.
- [ ] EL12 — Menggunakan pengurutan dan pagination pada daftar ruangan/pengajuan.
- [ ] EL13 — Membuat pengajuan melalui relasi pengguna login atau menetapkan user_id dari sesi, bukan mempercayai input peminjam.

Bukti yang dicari: model dan method relasi, query daftar, scope, serta penyimpanan fasilitas/pengajuan.

### 6 Query Builder transaksi dan collection

Pemilik: A2 pencarian, A3 bentrok jadwal, A4 statistik/laporan.
Sumber: S6 hlm. 5–14, 37–41; S7 hlm. 6–7, 12.

- [ ] QC01 — Menggunakan filter where, kondisi gabungan, dan pengurutan pada pencarian ruangan/peminjaman.
- [ ] QC02 — Memakai Query Builder DB::table() untuk sedikitnya satu kebutuhan nyata, misalnya rekap peminjaman per ruangan.
- [ ] QC03 — Menggunakan join dan agregasi seperti count()/groupBy() untuk laporan yang relevan.
- [ ] QC04 — Memakai query kondisional, misalnya when(), untuk filter tanggal/status yang opsional.
- [ ] QC05 — Menggunakan parameter binding atau Query Builder untuk nilai input; tidak menyusun SQL dari gabungan string input pengguna.
- [ ] QC06 — Menggunakan DB::transaction() pada proses persetujuan yang harus berhasil atau gagal sebagai satu kesatuan.
- [ ] QC07 — Menerapkan collection seperti map(), pluck(), atau groupBy() untuk kebutuhan nyata, misalnya memetakan data jadwal atau ringkasan laporan.

Bukti yang dicari: query pencarian/laporan, transaksi persetujuan, dan transformasi collection. Transaksi sudah ada dalam materi kuliah; jangan dilabeli seluruhnya sebagai materi baru di luar kelas.

### 7 Autentikasi session dan authorization

Pemilik: A1; semua anggota melindungi aksi modulnya.
Sumber: S9 hlm. 3–14, 16–20; S1 hlm. 14, 28–31; S2 hlm. 34–35; S8 hlm. 32.

- [ ] AU01 — Menyediakan registrasi, login, dan logout yang dapat digunakan.
- [ ] AU02 — Memahami route, controller, dan view autentikasi yang digunakan; catat starter kit atau implementasi aktualnya.
- [ ] AU03 — Menyimpan password dalam bentuk hash menggunakan fasilitas hashing Laravel.
- [ ] AU04 — Melindungi halaman internal dengan middleware auth dan halaman login/daftar dengan guest sesuai kebutuhan.
- [ ] AU05 — Mengambil identitas pengguna login melalui Auth atau $request->user().
- [ ] AU06 — Menjaga session login dengan benar dan memastikan logout mengakhiri akses sesi pengguna.
- [ ] AU07 — Menambahkan data profil NIM/NIP serta role; registrasi publik selalu menghasilkan peminjam.
- [ ] AU08 — Memeriksa hak akses di server agar peminjam tidak dapat menjalankan aksi admin melalui URL langsung.
- [ ] AU09 — Menggunakan policy untuk kepemilikan peminjaman atau izin pengelolaan ruangan, dan @can bila sesuai untuk tombol.
- [ ] AU10 — Membatasi percobaan login dengan rate limiting yang tersedia pada implementasi autentikasi.

Catatan sumber: PDF Breeze bertanggal 12 Oktober 2025, sedangkan target proyek Laravel 13. Catat hasil pemeriksaan kompatibilitas paket saat setup. Checklist ini tidak menyatakan semua perintah instalasi pada PDF lama pasti dapat dipakai tanpa penyesuaian. Konsep autentikasi tetap dapat dilacak meskipun starter kit aktual berbeda; jangan mencentang “menggunakan Breeze” jika proyek memakai implementasi lain.

Catatan penilaian: policy dan autentikasi sudah dibahas dalam materi. Role admin/peminjam adalah penerapan proyek; jelaskan secara spesifik bagian yang benar-benar dipelajari mandiri.

### 8 CRUD terintegrasi

Pemilik: A2 sebagai contoh modul CRUD lengkap; A3/A4 mengikuti alur bisnis.
Sumber: S8 hlm. 6, 12–30; S7 hlm. 9.

- [ ] CR01 — Daftar ruangan menampilkan data dari database.
- [ ] CR02 — Form tambah ruangan dapat menyimpan data valid.
- [ ] CR03 — Detail ruangan menampilkan record yang benar.
- [ ] CR04 — Form edit memuat data lama dan menyimpan perubahan valid.
- [ ] CR05 — Fasilitas yang tidak terpakai dapat dihapus dengan aturan relasi yang aman; fasilitas yang masih terhubung ditangani dengan jelas.
- [ ] CR06 — Ruangan yang memiliki riwayat dapat dinonaktifkan tanpa menghapus riwayatnya.
- [ ] CR07 — Filter pencarian tetap terbawa ketika pengguna berpindah halaman pagination.
- [ ] CR08 — Alur route → controller → validasi → model → view/redirect dapat dijelaskan anggota pemilik modul.
Pengajuan tidak perlu diberi edit/delete bebas hanya untuk meniru seluruh action resource. Ikuti aturan status dan kepemilikan aplikasi.

### 9 Pengujian sesuai materi

Pemilik: Semua anggota menguji modul sendiri; integrasi diperiksa bersama.
Sumber: S1 hlm. 37; S2 hlm. 49–50; S4 hlm. 62–63; S8 hlm. 35–38; S9 hlm. 20.

- [ ] TS01 — Feature test halaman daftar/detail ruangan memeriksa response dan data yang ditampilkan.
- [ ] TS02 — Feature test penyimpanan data valid memeriksa redirect dan record database.
- [ ] TS03 — Feature test data tidak valid memeriksa error validasi dan memastikan data tidak tersimpan.
- [ ] TS04 — Feature test perubahan serta penghapusan yang diizinkan memeriksa hasil database.
- [ ] TS05 — Feature test login memeriksa pengguna berhasil terautentikasi.
- [ ] TS06 — Feature test akses memeriksa guest diarahkan ke login dan peminjam ditolak dari aksi admin.
- [ ] TS07 — Feature test kepemilikan memastikan pengguna tidak dapat membuka/membatalkan pengajuan pengguna lain.
- [ ] TS08 — Menguji view atau komponen reusable yang dipakai, misalnya badge status atau kartu ruangan.

Bukti yang dicari: file tests/Feature/..., nama skenario, hasil eksekusi, tanggal, dan commit yang diuji. Gunakan database pengujian terpisah.

## B — Materi pilihan sesuai kebutuhan

Item berikut tetap berasal dari materi, tetapi tidak menjadi syarat selesai Bagian A. Jika tidak digunakan, isi alasan pada catatan pilihan. Kotak kosong berarti belum dibuktikan, bukan otomatis wajib dikerjakan.

### 10 Routing lanjutan

Sumber: S1 hlm. 6, 9–12, 15, 18–19, 21–22, 24–27, 29–36, 38.

- [ ] OP01 — Parameter opsional dan constraint route, termasuk constraint global bila dibutuhkan.
- [ ] OP02 — Route::view, Route::redirect, controller route group, atau invokable controller untuk kebutuhan yang sesuai.
- [ ] OP03 — API resource dan instalasi API routing hanya jika menyediakan endpoint API.
- [ ] OP04 — Singleton resource untuk profil, custom binding key/slug, scoped binding, explicit binding, atau enum binding bila sesuai model.
- [ ] OP05 — Konfigurasi middleware tingkat controller atau atribut middleware sebagai alternatif yang dipahami.
- [ ] OP06 — Fallback 404, route metadata, signed URL/URL utilities, serta route cache untuk kebutuhan yang jelas.

### 11 Blade lanjutan

Sumber: S2 hlm. 8–9, 11–13, 16–18, 20–21, 24, 27–30, 36–46, 48.

- [ ] OP07 — @switch, @isset, @empty, direktif session/environment, break/continue, serta conditional classes/attributes sesuai kebutuhan.
- [ ] OP08 — Named slots, attribute bag, @aware, dan dynamic components jika komponen memerlukannya.
- [ ] OP09 — Conditional includes, isolated include, @each, dan section inheritance lanjutan bila membantu tampilan.
- [ ] OP10 — @push/@stack, @once, serta direktif stack lain untuk aset halaman tertentu.
- [ ] OP11 — Mengirim data PHP ke JavaScript dengan Js::from() jika jadwal/grafik menggunakan JavaScript; sintaks escape Blade/JavaScript bila diperlukan.
- [ ] OP12 — Fragment, inline Blade, custom directive/custom if/custom echo, service injection, atau raw PHP hanya jika ada alasan penggunaan.
- [ ] OP13 — Memahami dan mencoba view:cache/view:clear untuk kebutuhan cache view.

### 12 Request dan response lanjutan

Sumber: S4 hlm. 6–11, 15–23, 40–41, 43–47, 52–61, 63–64.

- [ ] OP14 — Inspeksi path/URL/method, host, header, IP, content negotiation HTML/JSON/Markdown bila fitur memerlukannya.
- [ ] OP15 — Input JSON, typed helpers, date/interval/enum, dynamic properties, conditional input, dan merge() untuk normalisasi yang relevan.
- [ ] OP16 — Cookie request/response, response headers, dan cache-control hanya bila dibutuhkan.
- [ ] OP17 — JSON response untuk data jadwal/dashboard bila frontend memakainya.
- [ ] OP18 — Download atau inline response untuk laporan/bukti peminjaman beserta pengujiannya.
- [ ] OP19 — Streaming, generator, streamed JSON, SSE, dan konsumsi stream di frontend hanya jika fitur membutuhkannya.
- [ ] OP20 — Response macro dan konfigurasi middleware khusus jika ada kebutuhan berulang.

### 13 Database dan Eloquent lanjutan

Sumber: S6 hlm. 5, 10–12, 26–28, 32–33, 35, 38–41; S7 hlm. 8, 11–13; S8 hlm. 31.

- [ ] OP21 — Raw SQL berparameter, Query Builder insert/update/delete, upsert, serta increment/decrement jika lebih tepat untuk kebutuhan aktual.
- [ ] OP22 — Accessor/mutator dan serialization $hidden/$appends bila atribut turunan atau keluaran data membutuhkannya.
- [ ] OP23 — One-to-one, has-one/many-through, atau relasi polimorfik hanya bila desain data memang memerlukannya.
- [ ] OP24 — attach()/detach(), whereHas(), constrained eager loading, atau withCount() untuk relasi yang relevan.
- [ ] OP25 — Touching timestamps, modelKeys/contains, filter/reduce, atau global scope jika sesuai kebutuhan.
- [ ] OP26 — Soft delete dengan alur restore yang jelas bila dipilih; berbeda dari status operasional nonaktif.
- [ ] OP27 — Slug unik dan custom route key hanya bila ruangan diakses dengan slug.
- [ ] OP28 — chunk(), cursor, atau lazy collection untuk ekspor/proses data besar; tidak perlu membuat data besar hanya agar terpakai.

### 14 Autentikasi dan pola pengembangan pilihan

Sumber: S9 hlm. 4–8, 12, 14–18; S8 hlm. 34, 39–40; S1 hlm. 19.

- [ ] OP29 — Menggunakan Breeze jika sudah dipilih dan kompatibilitas paket telah diperiksa; catat versi serta penyesuaiannya.
- [ ] OP30 — Password reset dan email verification jika tersedia konfigurasi pengiriman email yang dapat diuji.
- [ ] OP31 — Memahami atau mengonfigurasi guard/session driver tambahan jika aplikasi memerlukannya.
- [ ] OP32 — Sanctum atau API authentication hanya jika aplikasi benar-benar menyediakan API berautentikasi.
- [ ] OP33 — Service layer seperti pengecekan ketersediaan dipakai bersama jika mengurangi duplikasi antara pengajuan dan persetujuan.
- [ ] OP34 — Action/Form Object hanya jika membantu struktur kode dan dapat dijelaskan oleh anggota.

### Catatan keputusan materi pilihan

| ID | Keputusan | Alasan atau fitur tujuan | Pemilik |
| --- | --- | --- | --- |
| OP… | Dipakai / Ditunda / Tidak relevan | … | … |

## C — Kebutuhan proyek dan tugas kelompok

Bagian ini memisahkan aturan bisnis serta luaran tugas dari daftar materi. Beberapa tekniknya mungkin sudah ada di slide; jangan otomatis menyebut semuanya sebagai konsep di luar kelas.

### 15 Aturan bisnis yang harus berjalan

- [ ] PJ01 — Pengajuan baru berstatus menunggu dan terhubung ke peminjam login.
- [ ] PJ02 — Pengajuan menunggu belum mengunci ruangan; jadwal terpakai berasal dari pengajuan disetujui.
- [ ] PJ03 — Pemeriksaan bentrok memakai ruangan sama dan interval bertumpang tindih: mulai lama < selesai baru DAN selesai lama > mulai baru. Jadwal yang tepat bersebelahan diperbolehkan jika tim tidak menetapkan jeda.
- [ ] PJ04 — Bentrok diperiksa ulang pada saat admin menyetujui.
- [ ] PJ05 — Dua persetujuan bersamaan untuk ruangan/waktu bertabrakan tidak dapat sama-sama berhasil; catat mekanisme penguncian/kontrol konkurensi yang dipilih dan hasil ujinya.
- [ ] PJ06 — Pengajuan hanya dapat disetujui/ditolak ketika status masih menunggu; alasan penolakan tersimpan.
- [ ] PJ07 — Peminjam hanya dapat membatalkan pengajuan sendiri yang masih menunggu.
- [ ] PJ08 — Ruangan nonaktif tidak menerima pengajuan baru; riwayat lama tetap dapat dibaca.
- [ ] PJ09 — Dashboard, laporan, dan jadwal memakai data database serta filter waktu/status yang konsisten.
- [ ] PJ10 — Tim menyepakati zona waktu aplikasi, batas tanggal pengajuan, jam operasional, dan aturan bila kapasitas/status ruangan berubah sebelum persetujuan.

Konsep mandiri untuk dipertimbangkan: penguncian data saat persetujuan bersamaan. Transaksi dasar, policy, feature test, dan service layer sudah muncul pada materi. Catat referensi dan penjelasan bagian tambahan yang benar-benar dipelajari mandiri.

### 16 GitHub dokumentasi dan demo

- [ ] GH01 — Satu repository kelompok tersedia dengan nama sesuai aplikasi.
- [ ] GH02 — Semua anggota melakukan commit dengan akun masing-masing.
- [ ] GH03 — Pekerjaan modul memakai branch dan perubahan direview melalui pull request.
- [ ] GH04 — .gitignore sesuai Laravel; .env dan rahasia tidak masuk repository.
- [ ] GH05 — README menjelaskan aplikasi, fitur, teknologi, kebutuhan lingkungan, instalasi, konfigurasi, migration/seeder, dan cara menjalankan.
- [ ] GH06 — Setiap anggota mengisi bukti penerapan materi yang menjadi tanggung jawabnya.
- [ ] GH07 — Konsep tambahan yang dipelajari mandiri ditulis beserta alasan, referensi, dan cara kerjanya.
- [ ] GH08 — Aplikasi dapat dipasang dan dijalankan ulang mengikuti README.
- [ ] GH09 — Presentasi mencakup tujuan, fitur, implementasi Laravel, database/relasi, GitHub, dan pembagian tugas.
- [ ] GH10 — Seluruh anggota mencoba alur demo dan dapat menjelaskan kode modulnya.

## D — Bukti penerapan dan rekap

### 17 Log bukti penerapan

Tambahkan satu baris per item atau per kelompok ID yang memakai bukti sama. Checklist dicentang setelah bukti diisi dan hasil dicoba, bukan hanya karena file sudah dibuat.

| ID materi | Pemilik | File dan method/route aktual | Commit atau PR | Cara cek dan hasil | Tanggal | Pemeriksa |
| --- | --- | --- | --- | --- | --- | --- |
| RT… / BL… | … | … | … | … | … | … |
| RQ… / DB… | … | … | … | … | … | … |
| EL… / QC… | … | … | … | … | … | … |
| AU… / CR… / TS… | … | … | … | … | … | … |
| OP… / PJ… / GH… | … | … | … | … | … | … |

### 18 Rekap progres

Isi angka dari checklist saat review kelompok. Tanda — berarti belum dihitung. Persentase target utama = jumlah item A yang dicentang / jumlah seluruh item A × 100%. Materi pilihan dihitung terpisah agar tidak dianggap kekurangan fitur wajib.

| Bagian | Selesai / total | Status singkat |
| --- | --- | --- |
| A1 Routing dan controller | — | Belum diaudit |
| A2 Blade | — | Belum diaudit |
| A3 Request dan response | — | Belum diaudit |
| A4 Migration | — | Belum diaudit |
| A5 Eloquent | — | Belum diaudit |
| A6 Query dan collection | — | Belum diaudit |
| A7 Autentikasi | — | Belum diaudit |
| A8 CRUD | — | Belum diaudit |
| A9 Pengujian | — | Belum diaudit |
| B Materi pilihan yang dipilih | — | Tentukan pilihan |
| C Aturan bisnis dan luaran | — | Belum diaudit |

### 19 Catatan review kelompok

Tanggal: …
Anggota yang hadir: …

- Materi yang berhasil dibuktikan: …
- Kendala atau bug: …
- Materi pilihan yang diputuskan tidak digunakan dan alasannya: …
- Pekerjaan berikutnya dan penanggung jawab: …
- Referensi mandiri yang perlu dibaca/dijelaskan: …
