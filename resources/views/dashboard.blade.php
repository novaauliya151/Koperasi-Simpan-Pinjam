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
                                    Pokok pinjaman: Rp {{ number_format(
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
                                <span class="stat-note">Keanggotaan terverifikasi</span>
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
            </div>

            <div class="row g-3 mb-3">
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
                                <span class="stat-label">Total simpanan tercatat</span>
                                <strong class="stat-value">
                                    Rp {{ number_format((float) $data['savingsTotal'], 0, ',', '.') }}
                                </strong>
                                <span class="stat-note">
                                    {{ number_format($data['savingsTransactions']) }} setoran sah
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card stat-card stat-card-rose h-100">
                        <div class="card-body p-3">
                            <span class="stat-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="m9 9 6 6m0-6-6 6" />
                                </svg>
                            </span>
                            <div class="stat-copy">
                                <span class="stat-label">Setoran dibatalkan</span>
                                <strong class="stat-value">
                                    {{ number_format($data['cancelledSavings']) }}
                                </strong>
                                <span class="stat-note">Tetap tersimpan untuk audit</span>
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
                                    Pokok: Rp {{ number_format((float) $data['activeLoanTotal'], 0, ',', '.') }}
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
                                    <small>Catatan pencairan dan tenor</small>
                                </span>
                                <b>›</b>
                            </a>
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
                                    Pokok Rp {{ number_format(
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
                            @if ($data['recentLoans']->isNotEmpty())
                                <div class="border-top mt-3 pt-3">
                                    <div class="small text-secondary mb-2">Pinjaman terbaru</div>
                                    @foreach ($data['recentLoans'] as $loan)
                                        <a
                                            href="{{ route('anggota.pinjaman.show', $loan) }}"
                                            class="d-flex justify-content-between gap-2 small mb-2 text-decoration-none"
                                        >
                                            <span class="text-truncate">{{ $loan->nomor_pinjaman }}</span>
                                            <span class="text-nowrap">
                                                Rp {{ number_format((float) $loan->pokok, 0, ',', '.') }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        @endif
    </section>
</x-layouts.app>
