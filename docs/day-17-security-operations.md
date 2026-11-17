# Day 17 Security and Operations

Catatan ini menyiapkan pemeriksaan keamanan, kinerja, backup, restore, dan kandidat demo. Hasil yang belum dijalankan dicatat terpisah agar tidak dianggap lulus.

## Pemeriksaan keamanan

- Route transaksi memakai middleware `auth`, `active`, dan role. Cakupan data anggota diperiksa pada daftar, detail pinjaman, bukti pembayaran, dan bukti simpanan.
- Tes fitur mencakup akses URL lintas peran, akses ID milik anggota lain, login akun nonaktif, logout, penonaktifan akun saat sesi berjalan, validasi server, pembatasan percobaan login, dan middleware CSRF pada route web.
- Tes tidak menampilkan kata sandi atau detail exception pada respons validasi login.
- Jalankan `php artisan test --filter=AuthenticationTest`, `php artisan test --filter=PaymentCorrectionsTest`, dan `php artisan test --filter=SavingsReversalsTest` setelah runner PHPUnit dapat membaca bootstrap.

## Kinerja dan tampilan

PRD menetapkan target persentil ke-95 maksimal 3 detik untuk halaman daftar/filter dengan 10.000 transaksi dan 10 pengguna bersamaan di lingkungan uji yang disepakati.

Tes `SavingsPaginationPerformanceTest` menyiapkan 10.000 setoran sintetis, memeriksa pagination, filter, struktur responsif, jumlah query, dan waktu satu permintaan. Tes satu proses ini bukan pengukuran persentil ke-95 atau beban 10 pengguna bersamaan.

Daftar anggota, simpanan, pinjaman, jenis simpanan, ketentuan, dan log menggunakan pagination. Tabel memakai wrapper responsif Bootstrap. Pemeriksaan tampilan browser pada desktop dan ponsel masih perlu dijalankan pada perangkat atau browser uji.

## Backup dan restore MySQL

Lakukan prosedur ini hanya dengan database uji sumber yang berisi data sintetis dan database restore terpisah. Penanggung jawabnya operator uji yang ditunjuk pemilik proyek. Jangan menjalankan restore ke database sumber.

1. Siapkan folder backup dengan akses terbatas di luar repository. Pastikan database yang dituju adalah database uji.
2. Dari Command Prompt, buat dump konsisten. Ganti `DB_USER`, `DB_NAME`, dan nama berkas dengan nilai lingkungan uji; opsi `--password` meminta kata sandi secara interaktif.

   ```text
   mysqldump --host=127.0.0.1 --user=DB_USER --password --single-transaction --routines --triggers --events DB_NAME > D:\secure-backups\koperasi-day17.sql
   ```

3. Buat database kosong khusus pemulihan, lalu impor dump ke database itu.

   ```text
   mysql --host=127.0.0.1 --user=DB_USER --password --execute="CREATE DATABASE koperasi_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql --host=127.0.0.1 --user=DB_USER --password koperasi_restore_test < D:\secure-backups\koperasi-day17.sql
   ```

4. Catat jumlah anggota, jumlah transaksi, total simpanan tercatat, total pembayaran tercatat, sisa pokok, dan sisa kewajiban dari sumber uji sebelum backup. Jalankan query atau laporan yang sama pada database hasil pemulihan dan pastikan hasilnya identik.
5. Catat waktu backup/restore, hash SHA-256 berkas dump, hasil perbandingan, operator, dan status. Hapus database pemulihan hanya setelah bukti hasil dicatat dan setelah memastikan nama database bukan sumber.

Contoh agregat untuk dibandingkan pada kedua database:

```sql
SELECT COUNT(*) AS jumlah_anggota FROM anggotas;
SELECT COALESCE(SUM(nominal), 0) AS saldo_simpanan
FROM transaksi_simpanans
WHERE status = 'tercatat';
SELECT COALESCE(SUM(nominal), 0) AS pembayaran_tercatat
FROM pembayarans
WHERE status = 'tercatat';
```

Sisa pokok dan kewajiban harus dibandingkan dari laporan pinjaman dan angsuran dengan filter yang sama pada sumber dan hasil restore.

## Kandidat demonstrasi

- `.env`, `.env.backup`, dan `.env.production` diabaikan Git. Jangan menambahkan salinan nilai rahasia ke dokumentasi, log, tangkapan layar, atau repository.
- Seeder memakai data contoh sintetis. Di environment `local`/`testing`, tiga password demo boleh sederhana dan sama untuk pengujian lokal. Di environment lain, ketiganya wajib berbeda dan masing-masing minimal 16 karakter.
- Tiga nilai tersebut hanya untuk akun demo. Jangan gunakan password sederhana di staging atau produksi; gunakan nilai privat yang berbeda dan kuat. Jangan masukkan password ke repository, dokumentasi, log, atau tangkapan layar.
- Untuk staging, gunakan `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, dan cookie sesi secure. Jangan gunakan dump data koperasi nyata untuk demo.
- Keterbatasan kandidat saat ini: uji p95 10 pengguna bersamaan, pemeriksaan visual browser lintas perangkat, dan pemulihan MySQL belum dijalankan di lingkungan uji terpisah.

## Status dan bukti

| Butir | Status | Bukti atau tindak lanjut |
| --- | --- | --- |
| Otorisasi, sesi, validasi, dan CSRF | Tes tersedia; eksekusi tertunda | Fitur tes terkait tersedia. PHPUnit saat ini gagal membuka `vendor/autoload.php` karena `is_readable()` mengembalikan `false` pada lingkungan runner. |
| Pagination 10.000 transaksi | Tes tersedia; eksekusi tertunda | `SavingsPaginationPerformanceTest`; belum menjadi bukti p95 atau beban serentak. |
| Target PRD 10 pengguna bersamaan | Belum diverifikasi | Sepakati host uji, dataset, dan alat beban; ukur p95 maksimal 3 detik. |
| Backup dan restore MySQL | Runbook tersedia; belum diuji | Jalankan hanya setelah database uji sumber dan target restore terpisah disiapkan. |
| Demo tanpa kredensial di Git | Perubahan diterapkan | Kredensial default dihapus dari seeder; seeder kini membutuhkan tiga nilai privat dari konfigurasi lokal. |
