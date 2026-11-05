# REST API Peminjaman Kelas FMIPA

API v1 menyediakan alur peminjaman dalam format JSON dengan database yang sama dengan web.

## Koneksi dan autentikasi

- Base URL: `http://127.0.0.1:8001/api/v1` atau `${APP_URL}/api/v1`.
- Header: `Accept: application/json`; untuk body JSON gunakan `Content-Type: application/json`.
- Selain login, semua endpoint memerlukan `Authorization: Bearer <token>`.
- Akun menggunakan NIM/NIP dan kata sandi yang sama dengan web. Registrasi tersedia melalui web; API tidak menyediakan pendaftaran atau perubahan role.
- Token Sanctum berlaku 7 hari. Login baru menerbitkan token baru; logout mencabut token saat ini tanpa mencabut token perangkat lain.
- API hanya menerima Bearer token. Session login web tidak digunakan untuk autentikasi API.
- Gunakan HTTPS saat dipublikasikan. Jangan menyimpan token atau kata sandi asli dalam repository.

### Login

`POST /login`

```json
{
  "identity_number": "00123456789",
  "password": "kata-sandi-akun",
  "device_name": "Postman laptop"
}
```

`identity_number` wajib berupa teks maksimal 30 karakter; `password` wajib berupa teks maksimal 1024 karakter; `device_name` wajib berupa teks maksimal 100 karakter. NIM/NIP tetap berupa teks agar nol di depan tersimpan.

Respons `200`:

```json
{
  "token_type": "Bearer",
  "token": "1|contoh-token",
  "expires_at": "2026-11-12T08:00:00+07:00",
  "data": {
    "id": 1,
    "name": "Nama pengguna",
    "identity_number": "00123456789",
    "email": "pengguna@example.com",
    "role": "mahasiswa"
  }
}
```

Token plaintext hanya ditampilkan ketika diterbitkan. Database menyimpan hash token.

### Akun dan logout

| Method | Endpoint | Hasil |
| --- | --- | --- |
| GET | `/me` | `200`, data akun pada properti `data` |
| POST | `/logout` | `204`, tanpa body; token saat ini dicabut |

## Ruangan

### Daftar dan detail

`GET /rooms?q=kelas&capacity=30&per_page=15&page=1`

- `q`: opsional, pencarian nama atau kode, maksimal 100 karakter.
- `capacity`: opsional, bilangan bulat minimal 1; menampilkan kapasitas minimal tersebut.
- `per_page`: 1–100, default 15; `page` mengikuti pagination Laravel.
- Hanya ruangan aktif yang ditampilkan.

`GET /rooms/{room}` mengembalikan satu ruangan aktif, atau `404` jika tidak ditemukan/nonaktif.

Data ruangan memuat `id`, `code`, `name`, `capacity`, `location`, `description`, dan `is_active`. Daftar menggunakan struktur `data`, `links`, dan `meta` dari pagination Laravel.

### Ketersediaan waktu

`GET /rooms/{room}/availability?date=2026-11-07&start_time=09:00&end_time=10:00`

Ketiga query wajib diisi. `date` menggunakan `YYYY-MM-DD`, minimal hari ini. Jam menggunakan `HH:MM` dalam WIB, antara 07:30–18:00; waktu selesai harus setelah waktu mulai.

Respons `200`:

```json
{
  "data": {
    "room_id": 1,
    "starts_at": "2026-11-07T09:00:00+07:00",
    "ends_at": "2026-11-07T10:00:00+07:00",
    "available": true,
    "notice": "Wajib memiliki surat izin atau surat peminjaman ruangan. Pengajuan tetap memerlukan persetujuan admin; ketersediaan diperiksa ulang saat pengajuan dan persetujuan."
  }
}
```

`available` menunjukkan waktu mulai masih di masa depan dan tidak beririsan dengan jadwal disetujui. Respons ini tidak memesan slot. Pengajuan `pending` tidak memblokir slot; keputusan akhir diperiksa ulang di dalam transaksi saat persetujuan. Detail peminjam lain tidak ditampilkan melalui endpoint ini.

## Peminjaman

### Membuat pengajuan

`POST /bookings`

```json
{
  "room_id": 1,
  "booking_date": "2026-11-07",
  "start_time": "09:00",
  "end_time": "10:00",
  "purpose": "Kegiatan mahasiswa",
  "participant_count": 25
}
```

| Field | Validasi |
| --- | --- |
| `room_id` | Wajib, ID ruangan yang ada dan aktif |
| `booking_date` | Wajib, `YYYY-MM-DD`, minimal hari ini |
| `start_time` | Wajib, `HH:MM`, mulai 07:30 dan sebelum 18:00; waktu aktual harus masih mendatang |
| `end_time` | Wajib, `HH:MM`, setelah waktu mulai dan paling lambat 18:00 |
| `purpose` | Wajib, teks maksimal 500 karakter |
| `participant_count` | Opsional/null, bilangan bulat 1–200 dan tidak melebihi kapasitas; jika kosong menggunakan kapasitas ruangan |

Pengguna pemilik diambil dari token dan status selalu `pending`. Field tambahan seperti `user_id`, `status`, dan `reviewed_by` tidak digunakan sebagai input penyimpanan.

Respons `201` berisi `data` peminjaman. Data respons: `id`, `room_id`, `user_id`, `purpose`, `participant_count`, `starts_at`, `ends_at`, `status`, `reviewed_by`, dan `rejection_reason`. Timestamp menggunakan ISO 8601 dengan offset WIB.

### Riwayat dan detail

- `GET /bookings`: hanya peminjaman milik pengguna saat ini, termasuk jika pengguna tersebut admin.
- `GET /bookings/{booking}`: pemilik atau admin; pengguna lain mendapat `403`.
- `GET /admin/bookings`: seluruh peminjaman, khusus admin, termasuk jadwal kuliah yang disimpan sebagai booking.
- Kedua daftar menerima `status=pending|approved|rejected|cancelled`, `per_page=1..100` (default 15), dan `page`. Hasil diurutkan dari ID terbaru dan menggunakan `data`, `links`, serta `meta`.

### Peninjauan admin

`PATCH /admin/bookings/{booking}/review`

Persetujuan:

```json
{"decision": "approved"}
```

Penolakan:

```json
{"decision": "rejected", "rejection_reason": "Surat peminjaman belum sesuai."}
```

`rejection_reason` wajib untuk penolakan, maksimal 2000 karakter. Hanya pengajuan `pending` yang dapat ditinjau. Respons `200` memuat data terbaru.

Saat menyetujui, server mengunci ruangan dan pengajuan, lalu memeriksa ulang status, kapasitas, keaktifan ruangan, jam operasional, dan bentrok. Jika dua pengajuan menunggu pada slot sama, persetujuan pertama memblokir persetujuan kedua. Pemeriksaan memakai interval `[mulai, selesai)`: 09:00–10:00 boleh berdampingan dengan 10:00–11:00.

Peminjaman Sabtu/Minggu tetap diperbolehkan. Peminjam wajib memiliki surat izin/surat peminjaman dan admin memeriksanya sebelum menyetujui. API ini belum menyediakan unggah atau pemeriksaan dokumen otomatis.

## Kesalahan dan pembatasan request

| Status | Arti |
| --- | --- |
| `200` | Pembacaan data, login, atau peninjauan berhasil |
| `201` | Pengajuan dibuat |
| `204` | Logout berhasil |
| `401` | Token tidak ada, salah, kedaluwarsa, atau telah dicabut |
| `403` | Tidak memiliki hak akses |
| `404` | Endpoint atau data tidak ditemukan; termasuk ruangan nonaktif |
| `422` | Input atau aturan bisnis tidak terpenuhi, termasuk bentrok |
| `429` | Batas request tercapai; perhatikan header `Retry-After` |

Contoh `422`:

```json
{
  "message": "...",
  "errors": {"participant_count": ["Jumlah peserta melebihi kapasitas ruangan atau tidak valid."]}
}
```

Pesan dapat berbeda mengikuti field dan locale. Semua respons kesalahan untuk `/api/*` menggunakan JSON, termasuk jika header `Accept` tidak dikirim.

Login dibatasi 5 request per menit untuk kombinasi IP dan NIM/NIP serta 20 request per menit per IP. Endpoint terproteksi dibatasi 120 request per menit per pengguna. Gunakan cache bersama ketika menjalankan beberapa server aplikasi.

## Menggunakan Postman

1. Import `docs/postman/booking-kelas-fmipa.postman_collection.json`.
2. Atur variabel koleksi `base_url`, `identity_number`, `password`, `admin_identity_number`, dan `admin_password` sesuai akun yang sudah ada. `base_url` tidak memakai garis miring terakhir.
3. Jalankan **Login pengguna**; skrip respons menyimpan `token` otomatis. **Login admin** menyimpan `admin_token` terpisah.
4. Jalankan **Daftar ruangan**, lalu atur `room_id` ke ruangan aktif. Isi `booking_date` dengan tanggal mendatang; nilai contoh perlu disesuaikan.
5. Periksa ketersediaan, buat pengajuan; skrip menyimpan `booking_id` otomatis. Jalankan daftar/detail dan peninjauan menggunakan token admin.
6. Logout, lalu akses `/me` lagi untuk memeriksa `401`.

Tidak ada kredensial/token asli dalam koleksi. Request peninjauan mengubah status sungguhan; gunakan data pengujian yang Anda siapkan.

## Batas cakupan

API v1 mencakup alur peminjaman utama. Registrasi, reset kata sandi, CRUD ruangan/fasilitas/pengguna, pengelolaan jadwal kuliah, dan impor CSV tetap melalui web.

Referensi: [Sanctum](https://laravel.com/docs/13.x/sanctum), [routing](https://laravel.com/docs/13.x/routing), dan [API Resources](https://laravel.com/docs/13.x/eloquent-resources).

## Verifikasi implementasi

Pemeriksaan pada 5 November 2026:

- `php artisan test --compact --no-ansi`: **154 tes lulus, 1012 assertions**, termasuk 13 tes API.
- `npm.cmd run build`: berhasil.
- Laravel Pint pada seluruh file PHP yang ditambah/diubah: berhasil.
- `php artisan route:list --path=api --except-vendor`: **11 route API** terdaftar.
- `php artisan route:cache`: berhasil; cache route dibersihkan kembali setelah pemeriksaan.
- Migrasi `2026_11_05_000000_create_personal_access_tokens_table`: berhasil dijalankan pada database proyek.
- Pemeriksaan HTTP server lokal: akses ruangan tanpa token `401`, login tanpa input `422`, endpoint tidak dikenal `404` (semuanya JSON), dan halaman web `/` tetap `200`.

Tes otomatis memakai SQLite `:memory:`; hasil tersebut tidak membuktikan konkurensi MySQL atau pengujian browser/Postman langsung. Koleksi Postman disediakan untuk pengujian manual.
