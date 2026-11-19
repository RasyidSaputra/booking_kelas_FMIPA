# Simulasi impor jadwal kuliah FMIPA UNRI

CSV berisi 20 pola mingguan Senin-Jumat, masing-masing empat pertemuan pada 23 November-18 Desember 2026 (80 pertemuan).
Nama mata kuliah diambil dari sumber resmi di bawah. Hari, jam, kelas, peserta, dan penempatan ruangan adalah simulasi; bukan jadwal resmi UNRI. Durasi dua jam dibuat untuk uji impor dan tidak merepresentasikan bobot SKS.

## Penanggung jawab dan ruangan
Kolom nip_dosen menggunakan 1, yaitu nomor identitas akun admin lokal yang diterima importer sebagai penanggung jawab. Ini bukan NIP dosen resmi. Belum ada akun ber-role dosen pada database lokal saat berkas dibuat.
Ruangan FMIPA-101 sampai FMIPA-105 aktif dengan kapasitas 50. Peserta disimulasikan 30-45 orang. Tidak ada jadwal dosen maupun ruangan yang saling tumpang tindih dalam CSV.

## Cara impor
1. Masuk sebagai admin, buka Jadwal kuliah mingguan, lalu Impor CSV.
2. Unggah simulasi-jadwal-kuliah-fmipa-unri.csv dan periksa pratinjau.
3. Konfirmasi hanya jika ingin membuat jadwal simulasi pada database lokal.
4. Jika dipakai di database lain, sesuaikan kode ruangan dan nip_dosen dengan akun dosen/admin yang terdaftar. Impor setelah periode berakhir memerlukan tanggal baru.
Berkas dibuat tanpa mengimpor atau mengubah data database. Pembukaan ulang melalui Excel dapat mengubah format tanggal; pertahankan YYYY-MM-DD dan jam HH:mm.

## Sumber nama mata kuliah (diakses 19 November 2026)
- Kurikulum S1 Sistem Informasi: https://ilkom.fmipa.unri.ac.id/id/akademik-s1-sistem-informasi/
- Kurikulum S1 Fisika: https://fisika.fmipa.unri.ac.id/id/kurikulum-s1

| Mata kuliah | Program studi sumber | Sumber |
| --- | --- | --- |
| Arsitektur dan Organisasi Komputer | Sistem Informasi | https://ilkom.fmipa.unri.ac.id/id/akademik-s1-sistem-informasi/ |
| Konsep Pemrograman | Sistem Informasi | https://ilkom.fmipa.unri.ac.id/id/akademik-s1-sistem-informasi/ |
| Manajemen dan Organisasi | Sistem Informasi | https://ilkom.fmipa.unri.ac.id/id/akademik-s1-sistem-informasi/ |
| Teknologi Multimedia | Sistem Informasi | https://ilkom.fmipa.unri.ac.id/id/akademik-s1-sistem-informasi/ |
| Statistika Dan Probabilitas | Sistem Informasi | https://ilkom.fmipa.unri.ac.id/id/akademik-s1-sistem-informasi/ |
| Fisika Dasar I | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Biologi Dasar | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Kimia Dasar I | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Matematika I | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Statistika Dasar | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Fisika Modern | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Matematika Fisika 1 | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Mekanika Klasik I | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Elektronika I | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Optik | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Termodinamika I | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Fisika Komputasi I | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Fisika Eksperimen | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Teori Gelombang | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
| Mekanika Kuantum | Fisika | https://fisika.fmipa.unri.ac.id/id/kurikulum-s1 |
