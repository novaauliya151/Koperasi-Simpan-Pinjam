# Asumsi Simulasi Pinjaman Day 10

Aturan berikut digunakan hanya untuk prototipe proyek magang. Ini bukan tarif
universal dan bukan kebijakan resmi koperasi tertentu.

## Parameter

- Produk: koperasi konvensional.
- Metode jasa: flat; jasa bulanan dihitung dari pokok awal.
- Tarif simulasi: 1% per bulan.
- Tenor yang tersedia: 3, 6, atau 12 bulan.
- Pembulatan: ke Rupiah terdekat; sisa pembulatan pokok ditambahkan ke
  angsuran terakhir agar total pokok sama dengan pencairan.
- Biaya administrasi, denda, pelunasan awal, dan pembayaran sebagian belum
  termasuk dalam asumsi ini.

## Contoh manual

Pokok Rp1.000.000 dengan tenor 3 bulan:

| Angsuran | Pokok | Jasa flat (1%) | Total |
| --- | ---: | ---: | ---: |
| 1 | Rp333.333 | Rp10.000 | Rp343.333 |
| 2 | Rp333.333 | Rp10.000 | Rp343.333 |
| 3 | Rp333.334 | Rp10.000 | Rp343.334 |
| **Jumlah** | **Rp1.000.000** | **Rp30.000** | **Rp1.030.000** |

## Dasar dan batasan

Petunjuk Pelaksanaan Standar Operasional Manajemen Usaha Simpan Pinjam
Koperasi Kemenkop (2025) membahas sistem flat dan bunga menurun, serta memberi
contoh penggunaan tarif 1% per bulan. Contoh tersebut menjadi bahan ilustrasi,
bukan bukti bahwa 1% adalah tarif umum. Angka dan pilihan tenor di atas adalah
asumsi simulasi yang dipilih untuk mendemonstrasikan alur aplikasi.

Permenkop UKM Nomor 8 Tahun 2023 Pasal 27 menetapkan batas maksimal bunga
pinjaman KSP/USP konvensional sebesar 24% per tahun dan mengatur penetapannya
oleh Pengurus dalam rentang yang disetujui Rapat Anggota. Batas maksimum itu
bukan tarif yang dianjurkan atau tarif baku.

Sumber:

- [Petunjuk Pelaksanaan SOM Kemenkop 2025](https://jdih.kop.go.id/doc/download/file/1770612160_Petunjuk%20Pelaksanaan%20SOM_2025%20%282%29.pdf/t/peraturan)
- [Permenkop UKM Nomor 8 Tahun 2023 - JDIH BPK](https://peraturan.bpk.go.id/Details/260414/permenkop-ukm-no-8-tahun-2023)

Perhitungan jadwal dan sisa pinjaman dikerjakan terpisah pada Day 11. Jangan
mengaktifkan asumsi ini untuk transaksi koperasi nyata tanpa penetapan aturan
oleh koperasi terkait.
