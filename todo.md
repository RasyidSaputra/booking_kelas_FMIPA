Checklist Penerapan Materi Laravel
Proyek: Dashboard Peminjaman Ruangan FMIPA UNRI
Tim: 4 anggota
Target proyek: Laravel 13 dan PHP 8.4
Dasar checklist: 9 PDF materi dan latihan yang diberikan dosen.
Cara menggunakan
- Ubah - [ ] menjadi - [x] setelah materi diterapkan, dicoba, dan hasilnya sesuai.
- Isi bukti pada tabel pelacakan: path file aktual, nama method atau route, dan commit/PR bila tersedia.
- Semua item dimulai belum dicentang karena kode proyek belum diperiksa. Ini tidak berarti tim belum mengerjakannya.
- Bagian A adalah target penerapan yang disarankan untuk proyek, bukan daftar kewajiban baru dari dosen.
- Bagian B memuat materi pilihan. Tidak semua contoh, sintaks alternatif, atau fitur pada slide harus dipaksakan masuk aplikasi.
- Satu implementasi boleh membuktikan beberapa materi. Contoh: form tambah ruangan mencakup route, controller, request, validasi, Blade, dan Eloquent.
- Nama file dan method di bawah merupakan usulan; sesuaikan dengan struktur proyek yang sebenarnya.
Pembagian pemilik modul
Kode	Anggota	Modul
A1	Nama: …	Autentikasi, profil, hak akses, layout bersama
A2	Nama: …	Ruangan, fasilitas, pencarian
A3	Nama: …	Pengajuan dan riwayat peminjaman
A4	Nama: …	Persetujuan, jadwal, dashboard, laporan


Daftar sumber
Nomor halaman mengacu pada urutan halaman PDF, termasuk halaman judul.
Kode	File materi	Cakupan
S1	laravel-route-controller.pdf	Routing, controller, binding, middleware, resource, testing
S2	laravel-blade-templating.pdf	View, direktif, layout, komponen, form, aset, testing
S3	laravel-blade-templating-exercise.pdf	Latihan controller ke view, loop, layout, komponen
S4	laravel-request-response-revised.pdf	Input, validasi, unggahan, redirect, JSON, response, testing
S5	laravel-request-response-exercise.pdf	Latihan profil, validasi, old input, flash message, Form Request
S6	laravel-database-eloquent.pdf	Query Builder, migration, Eloquent, relasi, collection
S7	practice-database-eloquent.pdf	Latihan database, factory, scope, relasi, collection
S8	laravel-crud.pdf	CRUD lengkap, validasi, policy, pencarian, testing, pola opsional
S9	laravel-auth-breeze.pdf	Autentikasi, Breeze, middleware, session, keamanan, testing


A Target penerapan pada fitur utama
1 Routing dan controller
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
2 Blade dan tampilan
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
3 Request validasi dan response
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
4 Migration dan data contoh
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
5 Eloquent dan relasi
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
6 Query Builder transaksi dan collection
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
7 Autentikasi session dan authorization
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
8 CRUD terintegrasi
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
9 Pengujian sesuai materi
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
B Materi pilihan sesuai kebutuhan
Item berikut tetap berasal dari materi, tetapi tidak menjadi syarat selesai Bagian A. Jika tidak digunakan, isi alasan pada catatan pilihan. Kotak kosong berarti belum dibuktikan, bukan otomatis wajib dikerjakan.
10 Routing lanjutan
Sumber: S1 hlm. 6, 9–12, 15, 18–19, 21–22, 24–27, 29–36, 38.
- [ ] OP01 — Parameter opsional dan constraint route, termasuk constraint global bila dibutuhkan.
- [ ] OP02 — Route::view, Route::redirect, controller route group, atau invokable controller untuk kebutuhan yang sesuai.
- [ ] OP03 — API resource dan instalasi API routing hanya jika menyediakan endpoint API.
- [ ] OP04 — Singleton resource untuk profil, custom binding key/slug, scoped binding, explicit binding, atau enum binding bila sesuai model.
- [ ] OP05 — Konfigurasi middleware tingkat controller atau atribut middleware sebagai alternatif yang dipahami.
- [ ] OP06 — Fallback 404, route metadata, signed URL/URL utilities, serta route cache untuk kebutuhan yang jelas.
11 Blade lanjutan
Sumber: S2 hlm. 8–9, 11–13, 16–18, 20–21, 24, 27–30, 36–46, 48.
- [ ] OP07 — @switch, @isset, @empty, direktif session/environment, break/continue, serta conditional classes/attributes sesuai kebutuhan.
- [ ] OP08 — Named slots, attribute bag, @aware, dan dynamic components jika komponen memerlukannya.
- [ ] OP09 — Conditional includes, isolated include, @each, dan section inheritance lanjutan bila membantu tampilan.
- [ ] OP10 — @push/@stack, @once, serta direktif stack lain untuk aset halaman tertentu.
- [ ] OP11 — Mengirim data PHP ke JavaScript dengan Js::from() jika jadwal/grafik menggunakan JavaScript; sintaks escape Blade/JavaScript bila diperlukan.
- [ ] OP12 — Fragment, inline Blade, custom directive/custom if/custom echo, service injection, atau raw PHP hanya jika ada alasan penggunaan.
- [ ] OP13 — Memahami dan mencoba view:cache/view:clear untuk kebutuhan cache view.
12 Request dan response lanjutan
Sumber: S4 hlm. 6–11, 15–23, 40–41, 43–47, 52–61, 63–64.
- [ ] OP14 — Inspeksi path/URL/method, host, header, IP, content negotiation HTML/JSON/Markdown bila fitur memerlukannya.
- [ ] OP15 — Input JSON, typed helpers, date/interval/enum, dynamic properties, conditional input, dan merge() untuk normalisasi yang relevan.
- [ ] OP16 — Cookie request/response, response headers, dan cache-control hanya bila dibutuhkan.
- [ ] OP17 — JSON response untuk data jadwal/dashboard bila frontend memakainya.
- [ ] OP18 — Download atau inline response untuk laporan/bukti peminjaman beserta pengujiannya.
- [ ] OP19 — Streaming, generator, streamed JSON, SSE, dan konsumsi stream di frontend hanya jika fitur membutuhkannya.
- [ ] OP20 — Response macro dan konfigurasi middleware khusus jika ada kebutuhan berulang.
13 Database dan Eloquent lanjutan
Sumber: S6 hlm. 5, 10–12, 26–28, 32–33, 35, 38–41; S7 hlm. 8, 11–13; S8 hlm. 31.
- [ ] OP21 — Raw SQL berparameter, Query Builder insert/update/delete, upsert, serta increment/decrement jika lebih tepat untuk kebutuhan aktual.
- [ ] OP22 — Accessor/mutator dan serialization $hidden/$appends bila atribut turunan atau keluaran data membutuhkannya.
- [ ] OP23 — One-to-one, has-one/many-through, atau relasi polimorfik hanya bila desain data memang memerlukannya.
- [ ] OP24 — attach()/detach(), whereHas(), constrained eager loading, atau withCount() untuk relasi yang relevan.
- [ ] OP25 — Touching timestamps, modelKeys/contains, filter/reduce, atau global scope jika sesuai kebutuhan.
- [ ] OP26 — Soft delete dengan alur restore yang jelas bila dipilih; berbeda dari status operasional nonaktif.
- [ ] OP27 — Slug unik dan custom route key hanya bila ruangan diakses dengan slug.
- [ ] OP28 — chunk(), cursor, atau lazy collection untuk ekspor/proses data besar; tidak perlu membuat data besar hanya agar terpakai.
14 Autentikasi dan pola pengembangan pilihan
Sumber: S9 hlm. 4–8, 12, 14–18; S8 hlm. 34, 39–40; S1 hlm. 19.
- [ ] OP29 — Menggunakan Breeze jika sudah dipilih dan kompatibilitas paket telah diperiksa; catat versi serta penyesuaiannya.
- [ ] OP30 — Password reset dan email verification jika tersedia konfigurasi pengiriman email yang dapat diuji.
- [ ] OP31 — Memahami atau mengonfigurasi guard/session driver tambahan jika aplikasi memerlukannya.
- [ ] OP32 — Sanctum atau API authentication hanya jika aplikasi benar-benar menyediakan API berautentikasi.
- [ ] OP33 — Service layer seperti pengecekan ketersediaan dipakai bersama jika mengurangi duplikasi antara pengajuan dan persetujuan.
- [ ] OP34 — Action/Form Object hanya jika membantu struktur kode dan dapat dijelaskan oleh anggota.
Catatan keputusan materi pilihan
ID	Keputusan	Alasan atau fitur tujuan	Pemilik
OP…	Dipakai / Ditunda / Tidak relevan	…	…


C Kebutuhan proyek dan tugas kelompok
Bagian ini memisahkan aturan bisnis serta luaran tugas dari daftar materi. Beberapa tekniknya mungkin sudah ada di slide; jangan otomatis menyebut semuanya sebagai konsep di luar kelas.
15 Aturan bisnis yang harus berjalan
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
16 GitHub dokumentasi dan demo
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
D Bukti penerapan dan rekap
17 Log bukti penerapan
Tambahkan satu baris per item atau per kelompok ID yang memakai bukti sama. Checklist dicentang setelah bukti diisi dan hasil dicoba, bukan hanya karena file sudah dibuat.
ID materi	Pemilik	File dan method/route aktual	Commit atau PR	Cara cek dan hasil	Tanggal	Pemeriksa
RT… / BL…	…	…	…	…	…	…
RQ… / DB…	…	…	…	…	…	…
EL… / QC…	…	…	…	…	…	…
AU… / CR… / TS…	…	…	…	…	…	…
OP… / PJ… / GH…	…	…	…	…	…	…


18 Rekap progres
Isi angka dari checklist saat review kelompok. Tanda — berarti belum dihitung. Persentase target utama = jumlah item A yang dicentang / jumlah seluruh item A × 100%. Materi pilihan dihitung terpisah agar tidak dianggap kekurangan fitur wajib.
Bagian	Selesai / total	Status singkat
A1 Routing dan controller	—	Belum diaudit
A2 Blade	—	Belum diaudit
A3 Request dan response	—	Belum diaudit
A4 Migration	—	Belum diaudit
A5 Eloquent	—	Belum diaudit
A6 Query dan collection	—	Belum diaudit
A7 Autentikasi	—	Belum diaudit
A8 CRUD	—	Belum diaudit
A9 Pengujian	—	Belum diaudit
B Materi pilihan yang dipilih	—	Tentukan pilihan
C Aturan bisnis dan luaran	—	Belum diaudit


19 Catatan review kelompok
Tanggal: …
Anggota yang hadir: …
- Materi yang berhasil dibuktikan: …
- Kendala atau bug: …
- Materi pilihan yang diputuskan tidak digunakan dan alasannya: …
- Pekerjaan berikutnya dan penanggung jawab: …
- Referensi mandiri yang perlu dibaca/dijelaskan: …