<x-layouts.app title="Dashboard | Koperasi">
    <section class="dashboard-page">
        <div class="dashboard-heading mb-3">
            <div>
                <h1>Dashboard</h1>
                <p>
                    @if ($user->role === 'admin')
                        Ringkasan kondisi dan aktivitas Koperasi Simpan Pinjam.
                    @elseif ($user->role === 'petugas')
                        Ringkasan pekerjaan dan status keanggotaan koperasi.
                    @else
                        Ringkasan simpanan dan keanggotaan Anda.
                    @endif
                </p>
            </div>
        </div>

        @if ($user->role === 'petugas')
            <form
                method="GET"
                action="{{ route('petugas.dashboard') }}"
                class="card card-body p-3 mb-3"
            >
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-auto">
                        <label for="periode" class="form-label mb-1">Periode ringkasan</label>
                        <input
                            id="periode"
                            name="periode"
                            type="month"
                            value="{{ $data['selectedPeriod'] }}"
                            class="form-control {{ $data['periodError'] ? 'is-invalid' : '' }}"
                            required
                        >
                        @if ($data['periodError'])
                            <div class="small text-danger mt-1">
                                {{ $data['periodError'] }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12 col-sm-auto">
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                        <a
                            href="{{ route('petugas.dashboard') }}"
                            class="btn btn-outline-secondary"
                        >
                            Bulan ini
                        </a>
                    </div>
                </div>
            </form>
        @endif

        @if ($user->role === 'admin')
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="card stat-card stat-card-cyan h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 8h18v11H3zM3 11h18M7 15h3" />
                                    <path d="M6 8V5h14v3" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Total simpanan</span>
                                <strong class="stat-value">
                                    Rp {{ number_format((float) $data['savingsTotal'], 0, ',', '.') }}
                                </strong>
                                <span class="stat-note">
                                    {{ number_format($data['savingsTransactions']) }} setoran sah ·
                                    {{ number_format($data['cancelledSavings']) }} dibatalkan
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="card stat-card stat-card-green h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 7h16v13H4zM4 10h16M8 15h4" />
                                    <path d="M7 7V4h12v3" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Pinjaman aktif</span>
                                <strong class="stat-value">
                                    {{ number_format($data['activeLoans']) }}
                                </strong>
                                <span class="stat-note">
                                    Sisa pokok: Rp {{ number_format(
                                        (float) $data['activeLoanTotal'],
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="card stat-card stat-card-yellow h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="9" cy="8" r="3.5" />
                                    <path
                                        d="M2 20a7 7 0 0 1 14 0M16 5a3.5 3.5 0 0 1 0 7M18 14a5 5 0 0 1 4 5"
                                    />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Anggota aktif</span>
                                <strong class="stat-value">
                                    {{ number_format($data['activeMembers']) }}
                                </strong>
                                <span class="stat-note">
                                    dari {{ number_format($data['members']) }} anggota
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="card stat-card stat-card-rose h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Perlu verifikasi</span>
                                <strong class="stat-value">
                                    {{ number_format($data['pendingMembers']) }}
                                </strong>
                                <span class="stat-note">Pendaftaran anggota menunggu</span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="card stat-card stat-card-cyan h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon stat-icon-blue">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="9" cy="8" r="3.5" />
                                    <path d="M2 20a7 7 0 0 1 14 0M17 8h5m-2.5-2.5v5" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Total akun</span>
                                <strong class="stat-value">
                                    {{ number_format($data['accounts']) }}
                                </strong>
                                <span class="stat-note">
                                    {{ number_format($data['activeAccounts']) }} akun aktif
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="row g-3 mb-3" id="ringkasan-laporan">
                <div class="col-lg-8">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div>
                                <h2>Tren simpanan</h2>
                                <p>Jumlah transaksi simpanan selama enam bulan terakhir</p>
                            </div>
                            <span class="period-label">6 bulan</span>
                        </div>
                        <div class="chart-panel">
                            @if ($data['hasSavingsActivity'])
                                <div
                                    class="bar-chart"
                                    role="img"
                                    aria-label="Grafik simpanan enam bulan terakhir"
                                >
                                    @foreach ($data['monthlySavings'] as $month)
                                        <div class="bar-column">
                                            <span class="bar-value">
                                                {{ $month['amount'] > 0
                                                    ? 'Rp '.number_format($month['amount'], 0, ',', '.')
                                                    : '' }}
                                            </span>
                                            <div class="bar-track">
                                                <div
                                                    class="bar-fill"
                                                    style="height: {{ $month['height'] }}%"
                                                ></div>
                                            </div>
                                            <span class="bar-label">{{ $month['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="chart-empty">
                                    <span class="chart-empty-mark">—</span>
                                    <strong>Belum ada transaksi simpanan</strong>
                                    <span>Grafik akan terisi setelah transaksi dicatat.</span>
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
                <div class="col-lg-4">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div>
                                <h2>Aktivitas terbaru</h2>
                                <p>Perubahan terakhir di sistem</p>
                            </div>
                            <a href="{{ route('admin.activity-logs.index') }}">Lihat semua</a>
                        </div>
                        <div
                            class="list-group list-group-flush activity-list
                                {{ $data['recentActivity']->isEmpty() ? 'activity-list-empty' : '' }}"
                        >
                            @forelse ($data['recentActivity'] as $activity)
                                <div class="list-group-item activity-row">
                                    <span class="activity-dot"></span>
                                    <div class="activity-copy">
                                        <strong>{{ str_replace('.', ' ', $activity->aksi) }}</strong>
                                        <span>{{ $activity->user?->name ?? 'Akun dihapus' }}</span>
                                    </div>
                                    <time>{{ $activity->terjadi_pada->format('d/m H:i') }}</time>
                                </div>
                            @empty
                                <div class="empty-state">Belum ada aktivitas yang tercatat.</div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div>
                                <h2>Akses cepat</h2>
                                <p>
                                    Ketentuan {{ ucfirst($data['loanTerms']['metode']) }}
                                    {{ number_format(
                                        (float) $data['loanTerms']['jasa_flat_bulanan'],
                                        2,
                                        ',',
                                        '.',
                                    ) }}% per bulan · tenor
                                    {{ implode(', ', $data['loanTerms']['tenor_bulan']) }} bulan
                                </p>
                            </div>
                        </div>
                        <div class="shortcut-list">
                            <a href="{{ route('admin.accounts.index') }}">
                                <span class="shortcut-icon shortcut-blue">AK</span>
                                <span><strong>Kelola akun</strong><small>Peran dan akses pengguna</small></span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('admin.saving-types.index') }}">
                                <span class="shortcut-icon shortcut-green">SP</span>
                                <span><strong>Jenis simpanan</strong><small>Produk dan ketentuannya</small></span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('admin.loan-terms.index') }}">
                                <span class="shortcut-icon shortcut-blue">PJ</span>
                                <span><strong>Ketentuan pinjaman</strong><small>Tarif dan pilihan tenor</small></span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('admin.reports.index') }}">
                                <span class="shortcut-icon shortcut-green">LP</span>
                                <span>
                                    <strong>Laporan koperasi</strong>
                                    <small>Anggota, simpanan, pinjaman, dan angsuran</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('admin.dashboard') }}#ringkasan-laporan">
                                <span class="shortcut-icon shortcut-blue">GR</span>
                                <span>
                                    <strong>Ringkasan grafik</strong>
                                    <small>Tren simpanan dan aktivitas sistem</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('admin.activity-logs.index') }}">
                                <span class="shortcut-icon shortcut-green">LG</span>
                                <span><strong>Log aktivitas</strong><small>Jejak perubahan sistem</small></span>
                                <b>›</b>
                            </a>
                        </div>
                    </section>
                </div>
            </div>

        @elseif ($user->role === 'petugas')
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-cyan h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon stat-icon-blue">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="9" cy="8" r="3.5" />
                                    <path
                                        d="M2 20a7 7 0 0 1 14 0M16 5a3.5 3.5 0 0 1 0 7M18 14a5 5 0 0 1 4 5"
                                    />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Total anggota</span>
                                <strong class="stat-value">{{ number_format($data['members']) }}</strong>
                                <span class="stat-note">Semua status keanggotaan</span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-yellow h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Menunggu verifikasi</span>
                                <strong class="stat-value">
                                    {{ number_format($data['pendingMembers']) }}
                                </strong>
                                <span class="stat-note">Pendaftaran perlu ditinjau</span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-green h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6" />
                                    <circle cx="12" cy="12" r="9" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Anggota aktif</span>
                                <strong class="stat-value">
                                    {{ number_format($data['activeMembers']) }}
                                </strong>
                                <span class="stat-note">Keanggotaan terverifikasi</span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-cyan h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon stat-icon-blue">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 8h18v11H3zM3 11h18M7 15h3" />
                                    <path d="M6 8V5h14v3" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">
                                    Setoran sah · {{ $data['periodLabel'] }}
                                </span>
                                <strong class="stat-value">
                                    Rp {{ number_format((float) $data['savingsTotal'], 0, ',', '.') }}
                                </strong>
                                <span class="stat-note">
                                    {{ number_format($data['savingsTransactions']) }} setoran ·
                                    {{ number_format($data['reversedSavings']) }} koreksi
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-green h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="M7 10h10M7 14h5" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Pinjaman aktif</span>
                                <strong class="stat-value">{{ number_format($data['activeLoans']) }}</strong>
                                <span class="stat-note">
                                    Sisa pokok: Rp {{ number_format(
                                        (float) $data['activeLoanTotal'],
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-cyan h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon stat-icon-blue">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 5h18v14H3zM7 9h10M7 13h6" />
                                    <circle cx="18" cy="16" r="3" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">
                                    Pembayaran sah · {{ $data['periodLabel'] }}
                                </span>
                                <strong class="stat-value">
                                    Rp {{ number_format((float) $data['paymentTotal'], 0, ',', '.') }}
                                </strong>
                                <span class="stat-note">
                                    {{ number_format($data['paymentCount']) }} pembayaran ·
                                    {{ number_format($data['paymentReversals']) }} koreksi
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-8">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div>
                                <h2>Anggota terbaru</h2>
                                <p>Data anggota yang baru ditambahkan</p>
                            </div>
                            <a href="{{ route('petugas.anggota.index') }}">Kelola anggota</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr><th>Nama</th><th>Nomor anggota</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['recentMembers'] as $member)
                                        <tr>
                                            <td>{{ $member->nama }}</td>
                                            <td>{{ $member->nomor_anggota }}</td>
                                            <td>
                                                <span class="status-pill">
                                                    {{ str_replace('_', ' ', $member->status_keanggotaan) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="empty-table">
                                                Belum ada data anggota.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-lg-4">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div><h2>Akses cepat</h2><p>Menu pekerjaan harian</p></div>
                        </div>
                        <div class="shortcut-list">
                            <a href="{{ route('petugas.anggota.index') }}">
                                <span class="shortcut-icon shortcut-blue">AG</span>
                                <span>
                                    <strong>Data anggota</strong>
                                    <small>Cari dan perbarui data</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('petugas.anggota.create') }}">
                                <span class="shortcut-icon shortcut-green">+</span>
                                <span>
                                    <strong>Tambah anggota</strong>
                                    <small>Daftarkan anggota baru</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('petugas.simpanan.create') }}">
                                <span class="shortcut-icon shortcut-green">SP</span>
                                <span>
                                    <strong>Catat setoran</strong>
                                    <small>Setoran anggota aktif</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('petugas.simpanan.index') }}">
                                <span class="shortcut-icon shortcut-blue">SH</span>
                                <span>
                                    <strong>Riwayat simpanan</strong>
                                    <small>Saldo, pembatalan, dan bukti</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('petugas.pinjaman.create') }}">
                                <span class="shortcut-icon shortcut-green">PJ</span>
                                <span>
                                    <strong>Catat pinjaman</strong>
                                    <small>Pinjaman yang sudah disetujui</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('petugas.pinjaman.index') }}">
                                <span class="shortcut-icon shortcut-blue">PH</span>
                                <span>
                                    <strong>Daftar pinjaman</strong>
                                    <small>Detail dan jadwal angsuran</small>
                                </span>
                                <b>›</b>
                            </a>
                            <a href="{{ route('petugas.pinjaman.index') }}">
                                <span class="shortcut-icon shortcut-green">BY</span>
                                <span>
                                    <strong>Pembayaran angsuran</strong>
                                    <small>Pilih pinjaman untuk menerima pembayaran</small>
                                </span>
                                <b>›</b>
                            </a>
                        </div>
                    </section>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div>
                                <h2>Pembayaran pada {{ $data['periodLabel'] }}</h2>
                                <p>Transaksi sah dan koreksi pada periode terpilih</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Anggota</th>
                                        <th>Nomor pinjaman</th>
                                        <th>Nomor pembayaran</th>
                                        <th class="text-end">Nominal</th>
                                        <th>Status</th>
                                        <th><span class="visually-hidden">Aksi</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['recentPayments'] as $payment)
                                        <tr>
                                            <td>
                                                {{ $payment->tanggal_pembayaran->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                {{ $payment->pinjaman?->anggota?->nama ?? '—' }}
                                            </td>
                                            <td>{{ $payment->pinjaman?->nomor_pinjaman ?? '—' }}</td>
                                            <td>{{ $payment->nomor_pembayaran }}</td>
                                            <td class="text-end text-nowrap">
                                                Rp {{ number_format(
                                                    (float) $payment->nominal,
                                                    0,
                                                    ',',
                                                    '.',
                                                ) }}
                                            </td>
                                            <td>
                                                <span class="status-pill">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a
                                                    href="{{ route(
                                                        'petugas.pembayaran.receipt',
                                                        $payment,
                                                    ) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    Bukti
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="empty-table">
                                                Belum ada pembayaran pada periode ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        @else
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-cyan h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon stat-icon-blue">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 8h18v11H3zM3 11h18M7 15h3" />
                                    <path d="M6 8V5h14v3" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Total simpanan tercatat</span>
                                <strong class="stat-value">
                                    Rp {{ number_format((float) $data['savingsTotal'], 0, ',', '.') }}
                                </strong>
                                <span class="stat-note">
                                    {{ number_format($data['savingsCount']) }} transaksi
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-green h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 7h16v13H4zM4 10h16M8 15h4" />
                                    <path d="M7 7V4h12v3" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Pinjaman aktif</span>
                                <strong class="stat-value">{{ number_format($data['activeLoans']) }}</strong>
                                <span class="stat-note">
                                    Sisa pokok Rp {{ number_format(
                                        (float) $data['activeLoanTotal'],
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </span>
                                <span class="stat-note mt-1">
                                    Sisa kewajiban Rp {{ number_format(
                                        (float) $data['activeLoanObligations'],
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-yellow h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path d="M4 21a8 8 0 0 1 16 0" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Status keanggotaan</span>
                                <strong class="stat-value stat-value-text">
                                    {{ $data['member']
                                        ? ucfirst(str_replace('_', ' ', $data['member']->status_keanggotaan))
                                        : 'Belum terhubung' }}
                                </strong>
                                <span class="stat-note">
                                    Nomor: {{ $data['member']?->nomor_anggota ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-4">
                    <section class="card dashboard-card h-100">
                        <div class="card-header">
                            <div>
                                <h2>Saldo per jenis simpanan</h2>
                                <p>Hanya transaksi yang tercatat</p>
                            </div>
                        </div>
                        <div class="list-group list-group-flush">
                            @forelse ($data['savingsBalances'] as $balance)
                                <div class="list-group-item d-flex justify-content-between gap-3">
                                    <span class="text-truncate">
                                        {{ $balance->jenis_simpanan_nama_snapshot
                                            ?? $balance->jenis?->nama
                                            ?? 'Simpanan' }}
                                    </span>
                                    <strong class="text-nowrap">
                                        Rp {{ number_format((float) $balance->saldo, 0, ',', '.') }}
                                    </strong>
                                </div>
                            @empty
                                <div class="empty-state p-4">
                                    Belum ada saldo simpanan yang tercatat.
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
                <div class="col-12 col-lg-8">
                    <section class="card dashboard-card h-100">
                        <div class="card-header">
                            <div>
                                <h2>Jadwal angsuran</h2>
                                <p>Tagihan yang belum lunas, termasuk yang terlambat</p>
                            </div>
                            <a href="{{ route('anggota.pinjaman.index') }}">Lihat pinjaman</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Jatuh tempo</th>
                                        <th>Pinjaman</th>
                                        <th class="text-end">Sisa tagihan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['upcomingInstallments'] as $item)
                                        <tr>
                                            <td>
                                                {{ $item['installment']->jatuh_tempo->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                <a href="{{ route(
                                                    'anggota.pinjaman.show',
                                                    $item['loan'],
                                                ) }}">
                                                    {{ $item['loan']->nomor_pinjaman }}
                                                </a>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                Rp {{ number_format(
                                                    (float) $item['outstanding'],
                                                    0,
                                                    ',',
                                                    '.',
                                                ) }}
                                            </td>
                                            <td>{{ $item['status'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="empty-table">
                                                Tidak ada tagihan angsuran yang belum lunas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-8">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div>
                                <h2>Riwayat simpanan</h2>
                                <p>Lima transaksi simpanan terakhir</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Nominal</th>
                                        <th>Status</th>
                                        <th>Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['recentSavings'] as $saving)
                                        <tr>
                                            <td>{{ $saving->tanggal_transaksi->format('d/m/Y') }}</td>
                                            <td>
                                                {{ $saving->jenis?->nama
                                                    ?? $saving->jenis_simpanan_nama_snapshot
                                                    ?? 'Simpanan' }}
                                            </td>
                                            <td>
                                                Rp {{ number_format(
                                                    (float) $saving->nominal,
                                                    0,
                                                    ',',
                                                    '.',
                                                ) }}
                                            </td>
                                            <td>
                                                <span class="status-pill">
                                                    {{ ucfirst($saving->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $saving->nomor_transaksi }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="empty-table">
                                                Belum ada transaksi simpanan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-lg-4">
                    <section class="card dashboard-card member-summary">
                        <div class="card-header">
                            <div><h2>Informasi anggota</h2><p>Data yang terhubung ke akun</p></div>
                        </div>
                        <div class="member-summary-body">
                            @if ($data['member'])
                                <strong>{{ $data['member']->nama }}</strong>
                                <span>{{ $data['member']->nomor_anggota }}</span>
                                <dl>
                                    <dt>Tanggal bergabung</dt>
                                    <dd>
                                        {{ $data['member']->tanggal_bergabung?->format('d F Y') ?? '—' }}
                                    </dd>
                                    <dt>Status</dt>
                                    <dd>
                                        {{ ucfirst(str_replace(
                                            '_',
                                            ' ',
                                            $data['member']->status_keanggotaan,
                                        )) }}
                                    </dd>
                                </dl>
                            @else
                                <p>Akun ini belum terhubung dengan data anggota.</p>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                                Lihat profil
                            </a>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a
                                    href="{{ route('anggota.simpanan.index') }}"
                                    class="btn btn-outline-primary btn-sm"
                                >
                                    Riwayat simpanan
                                </a>
                                <a
                                    href="{{ route('anggota.pinjaman.index') }}"
                                    class="btn btn-outline-primary btn-sm"
                                >
                                    Pinjaman saya
                                </a>
                            </div>
                            <div class="border-top mt-3 pt-3">
                                <div class="small text-secondary mb-2">Pinjaman terbaru</div>
                                @forelse ($data['recentLoans'] as $loan)
                                    <div class="small mb-2">
                                        <a
                                            href="{{ route('anggota.pinjaman.show', $loan) }}"
                                            class="d-flex justify-content-between gap-2
                                                text-decoration-none"
                                        >
                                            <span class="text-truncate">{{ $loan->nomor_pinjaman }}</span>
                                            <span class="text-nowrap">
                                                Rp {{ number_format(
                                                    (float) $loan->principal_remaining_dashboard,
                                                    0,
                                                    ',',
                                                    '.',
                                                ) }}
                                            </span>
                                        </a>
                                        <span class="text-secondary">
                                            Sisa kewajiban Rp {{ number_format(
                                                (float) $loan->unpaid_total_dashboard,
                                                0,
                                                ',',
                                                '.',
                                            ) }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="small text-secondary mb-0">Belum ada pinjaman.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <section class="card dashboard-card">
                        <div class="card-header">
                            <div>
                                <h2>Riwayat pembayaran pinjaman</h2>
                                <p>Lima pembayaran dan koreksi terakhir milik Anda</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nomor pinjaman</th>
                                        <th>Nomor pembayaran</th>
                                        <th class="text-end">Nominal</th>
                                        <th>Status</th>
                                        <th><span class="visually-hidden">Aksi</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['recentPayments'] as $payment)
                                        <tr>
                                            <td>
                                                {{ $payment->tanggal_pembayaran->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                {{ $payment->pinjaman?->nomor_pinjaman ?? '—' }}
                                            </td>
                                            <td>{{ $payment->nomor_pembayaran }}</td>
                                            <td class="text-end text-nowrap">
                                                Rp {{ number_format(
                                                    (float) $payment->nominal,
                                                    0,
                                                    ',',
                                                    '.',
                                                ) }}
                                            </td>
                                            <td>
                                                <span class="status-pill">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a
                                                    href="{{ route(
                                                        'anggota.pembayaran.receipt',
                                                        $payment,
                                                    ) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    Bukti
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="empty-table">
                                                Belum ada pembayaran pinjaman.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        @endif
    </section>
</x-layouts.app>
