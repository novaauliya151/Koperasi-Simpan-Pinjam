# Day 18 — Persiapan Evaluasi Pengguna

Dokumen ini menyiapkan skenario uji penerimaan untuk versi demo. Status hasil evaluasi dan komentar reviewer sengaja dibiarkan kosong sampai skenario benar-benar dicoba.

## Persiapan sesi

- Gunakan database demo terpisah dengan data sintetis. Jangan arahkan seeder ke database berisi data koperasi nyata.
- Untuk environment `local`/`testing`, isi tiga nilai `KOPERASI_DEMO_*_PASSWORD` di `.env` lokal; password sederhana seperti `password` boleh dipakai sementara. Jangan gunakan nilai itu di staging/produksi, dan jangan salin password ke dokumen atau repository.
- Setelah memastikan `.env` menunjuk ke database demo, jalankan `php artisan config:clear`, `php artisan migrate --seed`, lalu verifikasi akun dapat login. Perintah migrasi hanya untuk database demo yang disiapkan.
- Akun demo yang disediakan seeder: `admin@gmail.com` (admin), `petugas@gmail.com` (petugas), dan `anggota@gmail.com` (anggota). Kata sandi ditentukan lokal sesuai langkah di atas.
- Siapkan browser desktop dan ponsel/tablet bila tersedia. Catat tanggal, versi commit, lingkungan, dan nama reviewer.

## Checklist skenario

Isi status dengan **Lulus**, **Gagal**, atau **Belum diuji**. Untuk hasil gagal, tulis langkah untuk mengulang dan dampaknya.

| ID | Skenario | Hasil yang diharapkan | Status | Komentar / langkah reproduksi |
| --- | --- | --- | --- | --- |
| U01 | Login sebagai admin dan buka dashboard | Ringkasan administrasi tampil; menu sesuai peran |  |  |
| U02 | Admin membuka laporan dan menerapkan filter periode | Laporan menampilkan data sesuai filter dan dapat dicetak |  |  |
| U03 | Login sebagai petugas dan mendaftarkan anggota sintetis | Data anggota tersimpan dengan status awal yang benar |  |  |
| U04 | Petugas mencatat simpanan untuk anggota | Transaksi tercatat, saldo/history berubah, bukti dapat dibuka |  |  |
| U05 | Petugas mencatat pinjaman sesuai ketentuan aktif | Simulasi/jadwal tampil dan pinjaman tersimpan dengan aturan yang disepakati |  |  |
| U06 | Petugas mencatat pembayaran angsuran | Pembayaran tercatat, sisa kewajiban terbarui, bukti dapat dibuka |  |  |
| U07 | Login sebagai anggota dan lihat simpanan/pinjaman sendiri | Hanya data milik akun tersebut yang tampil |  |  |
| U08 | Anggota mencoba membuka ID transaksi atau pinjaman anggota lain | Akses ditolak atau data tidak ditemukan; data pihak lain tidak bocor |  |  |
| U09 | Coba alur utama pada desktop dan layar kecil | Form, tabel, navigasi, dan tombol tetap terbaca serta dapat digunakan |  |  |
| U10 | Logout, lalu akses kembali halaman terlindungi | Sesi berakhir dan pengguna diarahkan untuk login kembali |  |  |

## Catatan evaluasi

- Tanggal / sesi: ____________________
- Reviewer: ____________________
- Lingkungan dan browser: ____________________
- Versi commit: ____________________
- Catatan umum: ____________________

## Daftar revisi untuk Day 19

Masukkan hanya temuan nyata dari checklist. Jangan masukkan ide fitur tambahan sebagai perbaikan cepat.

| ID temuan | Prioritas | Skenario | Masalah dan dampak | Perbaikan yang disarankan | Hasil uji ulang |
| --- | --- | --- | --- | --- | --- |
|  | Kritis / Penting / Tambahan fitur |  |  |  |  |

Panduan prioritas:

- **Kritis:** menghalangi proses inti, merusak/menghilangkan data, atau membuka akses/data tanpa izin.
- **Penting:** alur masih dapat diselesaikan tetapi hasil, validasi, atau kejelasan penggunaan keliru.
- **Tambahan fitur:** permintaan baru di luar perilaku versi awal; catat sebagai backlog, bukan perbaikan cepat Day 18.

## Status Day 18

- Persiapan skenario dan format pencatatan: selesai.
- Akun demo: siap dibuat melalui seeder setelah database demo terpisah dan tiga nilai password lokal disiapkan.
- Evaluasi pengguna, perbaikan temuan, dan daftar prioritas berdasarkan hasil nyata: menunggu checklist diisi.
