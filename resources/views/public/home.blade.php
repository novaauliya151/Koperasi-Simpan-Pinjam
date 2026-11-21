<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Informasi layanan simpanan dan pinjaman koperasi.">
    <title>{{ config('app.name', 'Koperasi Simpan Pinjam') }}</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    @vite(['resources/css/app.css', 'resources/css/beranda.css'])
</head>
<body>
<div class="public-site">
    <div class="public-topline py-2">
        <div class="container d-flex justify-content-between">
            <span>Layanan simpanan dan pinjaman anggota</span>
            <a href="{{ route('login') }}">Masuk anggota</a>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg public-navbar sticky-top">
        <div class="container">
            <a class="public-brand" href="#beranda">
                <span class="public-mark" aria-hidden="true">K</span>
                <span>{{ config('app.name', 'Koperasi Simpan Pinjam') }}</span>
            </a>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navigasiBeranda"
                aria-controls="navigasiBeranda"
                aria-expanded="false"
                aria-label="Buka navigasi"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navigasiBeranda">
                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <a class="nav-link" href="#tentang">Tentang</a>
                    <a class="nav-link" href="#simpanan">Simpanan</a>
                    <a class="nav-link" href="#pinjaman">Pinjaman</a>
                    <a class="nav-link" href="#keanggotaan">Keanggotaan</a>
                    <a class="btn public-btn px-4 py-2" href="{{ route('login') }}">Masuk</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        {{-- Pembuka halaman menjelaskan layanan dan mengarahkan pengunjung ke portal anggota. --}}
        <section class="public-hero" id="beranda">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7">
                        <div class="public-eyebrow mb-3">Layanan keuangan berbasis anggota</div>
                        <h1 class="public-title">Simpan dan pinjam dengan tata kelola yang transparan</h1>
                        <p class="public-lead mt-4">
                            Koperasi melayani kebutuhan simpanan dan pembiayaan anggota
                            berdasarkan prinsip kehati-hatian, keterbukaan, dan kebersamaan.
                        </p>
                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a class="btn public-btn px-4 py-3" href="#keanggotaan">Cara bergabung</a>
                            <a class="btn public-btn-outline px-4 py-3" href="{{ route('login') }}">
                                Masuk ke portal
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="public-art" role="img" aria-label="Ilustrasi layanan koperasi">
                            <div class="public-art-card">
                                <div class="public-art-icon mb-4" aria-hidden="true">K</div>
                                <div class="public-eyebrow">Layanan anggota</div>
                                <div class="public-line"></div>
                                <div class="public-line public-line-short"></div>
                                <div class="d-flex gap-2 mt-4">
                                    <span class="badge text-bg-light">Simpanan</span>
                                    <span class="badge text-bg-light">Pinjaman</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="public-proof" aria-label="Ringkasan layanan">
            <div class="container">
                <div class="row row-cols-2 row-cols-lg-4">
                    <div class="col public-proof-item"><strong>Keanggotaan</strong><span>Layanan untuk anggota</span></div>
                    <div class="col public-proof-item"><strong>Simpanan</strong><span>Catatan setoran anggota</span></div>
                    <div class="col public-proof-item"><strong>Pembiayaan</strong><span>Sesuai ketentuan aktif</span></div>
                    <div class="col public-proof-item"><strong>Tata kelola</strong><span>Informasi yang terbuka</span></div>
                </div>
            </div>
        </section>

        <section class="public-section" id="tentang">
            <div class="container">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-7">
                        <div class="public-eyebrow mb-3">Tentang kami</div>
                        <h2 class="public-heading">Lembaga keuangan milik dan untuk anggota</h2>
                        <p class="public-copy mt-3">
                            Koperasi menghimpun simpanan dan menyalurkan pinjaman sesuai
                            kesepakatan, ketentuan, dan keputusan bersama para anggota.
                        </p>
                        <div class="row g-4 border-top pt-4 mt-4">
                            <div class="col-sm-6">
                                <h3 class="h6 fw-bold text-primary-emphasis">Visi</h3>
                                <p class="public-copy small">Memberikan layanan koperasi yang tertib dan dapat dipahami anggota.</p>
                            </div>
                            <div class="col-sm-6">
                                <h3 class="h6 fw-bold text-primary-emphasis">Misi</h3>
                                <p class="public-copy small">Menjaga pencatatan dan pelayanan berdasarkan ketentuan koperasi.</p>
                            </div>
                        </div>
                        <div class="public-callout p-3 mt-3 small">
                            Setiap anggota memiliki kedudukan yang setara dalam rapat anggota.
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="public-card p-5">
                            <div class="public-eyebrow mb-3">Layanan dalam satu portal</div>
                            <h3>Informasi tercatat dan mudah ditelusuri</h3>
                            <p class="mb-0">Anggota dan petugas melihat informasi sesuai peran masing-masing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="public-section public-muted" id="simpanan">
            <div class="container">
                <div class="public-eyebrow mb-2">Produk simpanan</div>
                <h2 class="public-heading">Pilihan simpanan anggota</h2>
                <p class="public-copy mt-3 mb-4">
                    Besaran setoran dan ketentuan penarikan mengikuti kebijakan koperasi.
                </p>
                <div class="row g-3">
                    <div class="col-md-6 col-xl-3">
                        <article class="public-card p-4">
                            <h3>Simpanan Pokok</h3>
                            <p class="mb-0">Setoran awal keanggotaan sesuai ketentuan.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <article class="public-card p-4">
                            <h3>Simpanan Wajib</h3>
                            <p class="mb-0">Setoran berkala sesuai ketentuan keanggotaan.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <article class="public-card p-4">
                            <h3>Simpanan Sukarela</h3>
                            <p class="mb-0">Simpanan fleksibel jika tersedia di koperasi.</p>
                        </article>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <article class="public-card p-4">
                            <h3>Simpanan Berjangka</h3>
                            <p class="mb-0">Periode simpanan mengikuti kesepakatan yang berlaku.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="public-section" id="pinjaman">
            <div class="container">
                <div class="row g-4 align-items-end mb-4">
                    <div class="col-lg-8">
                        <div class="public-eyebrow mb-2">Produk pinjaman</div>
                        <h2 class="public-heading">Pinjaman sesuai kebutuhan anggota</h2>
                    </div>
                    <div class="col-lg-4"><p class="public-copy mb-0">Pengajuan melalui verifikasi kemampuan bayar dan kelengkapan dokumen.</p></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><article class="public-card p-4"><h3>Pinjaman Produktif</h3><p class="mb-0">Untuk mendukung kegiatan usaha anggota.</p></article></div>
                    <div class="col-md-4"><article class="public-card p-4"><h3>Pinjaman Konsumtif</h3><p class="mb-0">Untuk kebutuhan rumah tangga yang terencana.</p></article></div>
                    <div class="col-md-4"><article class="public-card p-4"><h3>Pinjaman Pendidikan</h3><p class="mb-0">Untuk biaya pendidikan anggota atau keluarga.</p></article></div>
                </div>
                <p class="public-copy small mt-3">Syarat dan ketentuan mengikuti kebijakan serta keputusan koperasi.</p>
            </div>
        </section>

        {{-- Nilai simulasi hanya perkiraan berdasarkan aturan proyek yang sedang dikonfigurasi. --}}
        <section class="public-section public-muted" id="simulasi">
            <div class="container">
                <div class="public-eyebrow mb-2">Perencanaan pinjaman</div>
                <h2 class="public-heading">Simulasi angsuran bulanan</h2>
                <p class="public-copy mt-3">Masukkan rencana pinjaman untuk melihat perkiraan angsuran.</p>
                <div class="row g-0 public-card mt-4">
                    <div class="col-lg-7 p-4 p-md-5">
                        <label class="form-label fw-semibold" for="jumlahPinjaman">Jumlah pinjaman</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Rp</span>
                            <input class="form-control" id="jumlahPinjaman" type="number" min="1" placeholder="Masukkan jumlah pinjaman">
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="tenorPinjaman">Tenor pinjaman</label>
                                <select class="form-select" id="tenorPinjaman">
                                    @foreach ($loanTerms['tenors'] as $tenor)
                                        <option value="{{ $tenor }}">{{ $tenor }} bulan</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold" for="jasaPinjaman">Jasa per bulan</label>
                                <div class="input-group">
                                    <input class="form-control" id="jasaPinjaman" type="number" min="0" step="0.01" value="{{ $loanTerms['monthly_rate'] }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <button class="btn public-btn px-4 py-2 mt-4" id="hitungAngsuran" type="button">Hitung angsuran</button>
                    </div>
                    <div class="col-lg-5 p-4 p-md-5 text-white public-simulation-result">
                        <div class="small text-white-50">Perkiraan angsuran per bulan</div>
                        <div class="h2 mt-2" id="hasilAngsuran">Masukkan data simulasi</div>
                        <hr class="border-light opacity-25">
                        <p class="small text-white-50 mb-0">
                            Hasil ini hanya perkiraan, bukan persetujuan pinjaman.
                            Nilai final mengikuti ketentuan koperasi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="public-section" id="tata-kelola">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="public-eyebrow mb-2">Tata kelola</div>
                        <h2 class="public-heading">Informasi koperasi yang dapat diperiksa</h2>
                    </div>
                    <div class="col-lg-8"><div class="row g-3">
                        <div class="col-md-6"><article class="public-card p-4"><h3>Pengawasan berkala</h3><p class="mb-0">Kegiatan mengikuti pengawasan sesuai ketentuan koperasi.</p></article></div>
                        <div class="col-md-6"><article class="public-card p-4"><h3>Rapat anggota</h3><p class="mb-0">Keputusan penting dibahas bersama anggota.</p></article></div>
                        <div class="col-md-6"><article class="public-card p-4"><h3>Pembagian SHU</h3><p class="mb-0">Mengikuti keputusan rapat dan partisipasi anggota.</p></article></div>
                        <div class="col-md-6"><article class="public-card p-4"><h3>Laporan terbuka</h3><p class="mb-0">Ringkasan laporan tersedia sesuai kebijakan koperasi.</p></article></div>
                    </div></div>
                </div>
            </div>
        </section>

        <section class="public-section public-muted" id="keanggotaan">
            <div class="container">
                <div class="public-eyebrow mb-2">Keanggotaan</div>
                <h2 class="public-heading">Cara menjadi anggota</h2>
                <div class="row g-4 mt-3">
                    <div class="col-sm-6 col-lg-3"><div class="public-step">01</div><h3 class="h6 fw-bold">Isi formulir</h3><p class="public-copy small">Lengkapi data pendaftaran melalui petugas koperasi.</p></div>
                    <div class="col-sm-6 col-lg-3"><div class="public-step">02</div><h3 class="h6 fw-bold">Serahkan dokumen</h3><p class="public-copy small">Lampirkan dokumen identitas untuk verifikasi.</p></div>
                    <div class="col-sm-6 col-lg-3"><div class="public-step">03</div><h3 class="h6 fw-bold">Verifikasi</h3><p class="public-copy small">Petugas memeriksa data dan persyaratan.</p></div>
                    <div class="col-sm-6 col-lg-3"><div class="public-step">04</div><h3 class="h6 fw-bold">Aktivasi anggota</h3><p class="public-copy small">Keanggotaan diproses sesuai keputusan koperasi.</p></div>
                </div>
                <div class="public-callout d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-4 mt-3">
                    <span>Hubungi petugas untuk mengetahui dokumen dan persyaratan yang berlaku.</span>
                    <a class="btn public-btn px-4 py-2" href="{{ route('login') }}">Masuk anggota</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="public-footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <a class="public-brand text-white" href="#beranda">
                        <span class="public-mark border-light text-white">K</span>
                        <span>{{ config('app.name', 'Koperasi Simpan Pinjam') }}</span>
                    </a>
                    <p class="small mt-3 mb-0">Koperasi berbasis keanggotaan yang melayani simpanan dan pinjaman sesuai ketentuan bersama.</p>
                </div>
                <div class="col-lg-3"><h2 class="h6">Tautan</h2><p class="mb-2"><a href="#tentang">Tentang kami</a></p><p class="mb-2"><a href="#simpanan">Simpanan</a></p><p class="mb-2"><a href="#pinjaman">Pinjaman</a></p></div>
                <div class="col-lg-3"><h2 class="h6">Portal</h2><p class="small">Masuk untuk melihat informasi sesuai akun.</p><a class="btn public-btn px-3 py-2" href="{{ route('login') }}">Masuk</a></div>
            </div>
            <hr class="border-light opacity-25 mt-4">
            <div class="small text-white-50">© {{ now()->year }} {{ config('app.name', 'Koperasi Simpan Pinjam') }}</div>
        </div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simulasi memakai metode flat agar hasil mudah dibandingkan dengan ketentuan yang tampil.
    document.getElementById('hitungAngsuran')?.addEventListener('click', function () {
        const jumlah = Number(document.getElementById('jumlahPinjaman').value);
        const tenor = Number(document.getElementById('tenorPinjaman').value);
        const jasa = Number(document.getElementById('jasaPinjaman').value);
        const hasil = document.getElementById('hasilAngsuran');

        if (!jumlah || !tenor || jasa < 0) {
            hasil.textContent = 'Lengkapi data simulasi';
            return;
        }

        const total = jumlah + (jumlah * jasa / 100 * tenor);
        hasil.textContent = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(total / tenor);
    });
</script>
</body>
</html>

