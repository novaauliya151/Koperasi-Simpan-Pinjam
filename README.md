# Koperasi Simpan Pinjam

Aplikasi Laravel untuk pencatatan anggota, simpanan, pinjaman, dan pembayaran angsuran. Proyek menggunakan MySQL dan data uji fiktif.

## Persyaratan

- PHP 8.4 atau lebih baru dengan ekstensi standar Laravel dan `pdo_mysql`
- Composer
- MySQL 8
- Node.js dan npm

## Setup lokal

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Buat database MySQL kosong, misalnya `db_koperasi_simpan_pinjam`, lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_koperasi_simpan_pinjam
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan skema dan akun uji:

```powershell
php artisan migrate:fresh --seed
npm install
npm run build
composer run dev
```

Aplikasi tersedia di `http://localhost:8000`.

## Akun uji lokal

| Peran | Email | Kata sandi |
|---|---|---|
| Admin | `admin@koperasi.test` | `password` |
| Petugas | `petugas@koperasi.test` | `password` |
| Anggota | `anggota@koperasi.test` | `password` |

Akun di atas hanya untuk database lokal. Ganti kredensial sebelum penggunaan selain pengembangan.

## Catatan skema

Nominal uang menggunakan `DECIMAL(15,2)`. Tabel dan relasi memakai nama eksplisit agar tidak bergantung pada tebakan pluralisasi nama Indonesia. Transaksi tidak dihapus permanen; koreksi harus menyimpan referensi dan alasan. Tarif jasa pada pinjaman disimpan sebagai snapshot.

Seeder hanya membuat satu anggota fiktif dan tiga akun untuk pengembangan. Jangan masukkan data pribadi atau transaksi nyata ke database uji.
