<x-layouts.app title="Laporan | Koperasi Simpan Pinjam">
    <section class="dashboard-page">
        <div class="dashboard-heading mb-3 d-print-none">
            <div>
                <h1>Laporan Koperasi</h1>
                <p>Rekap anggota, transaksi simpanan, pinjaman, dan angsuran.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                Cetak laporan
            </button>
        </div>

        <nav class="nav nav-pills gap-2 mb-3 report-tabs d-print-none" aria-label="Jenis laporan">
            @foreach ([
                'anggota' => 'Anggota',
                'simpanan' => 'Simpanan',
                'pinjaman' => 'Pinjaman',
                'angsuran' => 'Angsuran',
            ] as $type => $label)
                <a
                    class="nav-link {{ $reportType === $type ? 'active' : '' }}"
                    href="{{ route('admin.reports.index', ['laporan' => $type]) }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <section class="card dashboard-card mb-3 report-filters d-print-none">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.index') }}">
                    <input type="hidden" name="laporan" value="{{ $reportType }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="dari" class="form-label">Dari tanggal</label>
                            <input
                                id="dari"
                                type="date"
                                name="dari"
                                value="{{ $filters['dari'] ?? '' }}"
                                class="form-control @error('dari') is-invalid @enderror"
                            >
                            @error('dari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-sm-6 col-xl-2">
                            <label for="sampai" class="form-label">Sampai tanggal</label>
                            <input
                                id="sampai"
                                type="date"
                                name="sampai"
                                value="{{ $filters['sampai'] ?? '' }}"
                                class="form-control @error('sampai') is-invalid @enderror"
                            >
                            @error('sampai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if (in_array($reportType, ['simpanan', 'pinjaman', 'angsuran'], true))
                            <div class="col-12 col-sm-6 col-xl-3">
                                <label for="anggota_id" class="form-label">Anggota</label>
                                <select id="anggota_id" name="anggota_id" class="form-select">
                                    <option value="">Semua anggota</option>
                                    @foreach ($members as $member)
                                        <option
                                            value="{{ $member->id }}"
                                            @selected(($filters['anggota_id'] ?? '') == $member->id)
                                        >
                                            {{ $member->nomor_anggota }} — {{ $member->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($reportType === 'anggota')
                            <div class="col-12 col-sm-6 col-xl-3">
                                <label for="status_keanggotaan" class="form-label">Status anggota</label>
                                <select id="status_keanggotaan" name="status_keanggotaan" class="form-select">
                                    <option value="">Semua status</option>
                                    @foreach ([
                                        'aktif' => 'Aktif',
                                        'menunggu_verifikasi' => 'Menunggu verifikasi',
                                        'tidak_aktif' => 'Tidak aktif',
                                        'keluar' => 'Keluar',
                                    ] as $status => $label)
                                        <option
                                            value="{{ $status }}"
                                            @selected(($filters['status_keanggotaan'] ?? '') === $status)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif ($reportType === 'simpanan')
                            <div class="col-12 col-sm-6 col-xl-3">
                                <label for="jenis_simpanan_id" class="form-label">Jenis simpanan</label>
                                <select id="jenis_simpanan_id" name="jenis_simpanan_id" class="form-select">
                                    <option value="">Semua jenis</option>
                                    @foreach ($savingTypes as $type)
                                        <option
                                            value="{{ $type->id }}"
                                            @selected(($filters['jenis_simpanan_id'] ?? '') == $type->id)
                                        >
                                            {{ $type->kode }} — {{ $type->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif (in_array($reportType, ['pinjaman', 'angsuran'], true))
                            <div class="col-12 col-sm-6 col-xl-3">
                                <label for="status_pinjaman" class="form-label">Status pinjaman</label>
                                <select id="status_pinjaman" name="status_pinjaman" class="form-select">
                                    <option value="">Semua status</option>
                                    @foreach (['aktif' => 'Aktif', 'lunas' => 'Lunas'] as $status => $label)
                                        <option
                                            value="{{ $status }}"
                                            @selected(($filters['status_pinjaman'] ?? '') === $status)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12 col-xl-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Terapkan filter</button>
                            <a
                                href="{{ route('admin.reports.index', ['laporan' => $reportType]) }}"
                                class="btn btn-outline-secondary"
                            >
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <article class="card dashboard-card report-paper">
            <header class="report-heading">
                <div class="report-organization">KOPERASI SIMPAN PINJAM</div>
                <h1>{{ $report['title'] }}</h1>
                <p>{{ $report['description'] }}</p>
                <div class="report-period">
                    Periode:
                    {{ $filters['dari'] ?? 'awal data' }}
                    sampai
                    {{ $filters['sampai'] ?? now()->toDateString() }}
                </div>
            </header>

            @if ($report['totals'])
                <div class="row g-2 mb-3 report-totals">
                    @foreach ($report['totals'] as $label => $amount)
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-secondary">{{ $label }}</div>
                                <strong>
                                    Rp {{ number_format((float) $amount, 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive report-table-wrap">
                <table class="table table-sm table-bordered align-middle mb-0 report-table">
                    @if ($reportType === 'anggota')
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nomor anggota</th>
                                <th>Nama</th>
                                <th>Tanggal bergabung</th>
                                <th>Status keanggotaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['rows'] as $member)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $member->nomor_anggota }}</td>
                                    <td>{{ $member->nama }}</td>
                                    <td>{{ $member->tanggal_bergabung?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $member->status_keanggotaan)) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        Tidak ada data anggota untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @elseif ($reportType === 'simpanan')
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Anggota</th>
                                <th>Jenis simpanan</th>
                                <th>Nomor transaksi</th>
                                <th>Status</th>
                                <th class="text-end">Perubahan saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['rows'] as $transaction)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $transaction->tanggal_transaksi->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $transaction->anggota?->nomor_anggota }} —
                                        {{ $transaction->anggota?->nama ?? 'Anggota dihapus' }}
                                    </td>
                                    <td>
                                        {{ $transaction->jenis_simpanan_nama_snapshot
                                            ?? $transaction->jenis?->nama
                                            ?? 'Simpanan' }}
                                    </td>
                                    <td>{{ $transaction->nomor_transaksi }}</td>
                                    <td>
                                        @if (
                                            $transaction->status === 'dibatalkan'
                                            && $transaction->pembatalan?->status === 'pembalikan'
                                        )
                                            Dibatalkan · setoran awal
                                        @else
                                            {{ ucfirst($transaction->status) }}
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if (
                                            $transaction->status === 'tercatat'
                                            || (
                                                $transaction->status === 'dibatalkan'
                                                && $transaction->pembatalan?->status === 'pembalikan'
                                            )
                                        )
                                            +
                                        @elseif ($transaction->status === 'pembalikan')
                                            −
                                        @endif
                                        Rp {{ number_format(
                                            $transaction->status === 'dibatalkan'
                                                && $transaction->pembatalan?->status !== 'pembalikan'
                                                ? 0
                                                : (float) $transaction->nominal,
                                            0,
                                            ',',
                                            '.',
                                        ) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        Tidak ada transaksi simpanan untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @elseif ($reportType === 'pinjaman')
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal cair</th>
                                <th>Nomor pinjaman</th>
                                <th>Anggota</th>
                                <th>Status</th>
                                <th class="text-end">Pokok cair</th>
                                <th class="text-end">Pembayaran neto periode</th>
                                <th class="text-end">Sisa pokok saat ini</th>
                                <th class="text-end">Sisa kewajiban saat ini</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['rows'] as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['loan']->tanggal_pencairan->format('d/m/Y') }}</td>
                                    <td>{{ $row['loan']->nomor_pinjaman }}</td>
                                    <td>
                                        {{ $row['loan']->anggota?->nomor_anggota }} —
                                        {{ $row['loan']->anggota?->nama ?? 'Anggota dihapus' }}
                                    </td>
                                    <td>{{ ucfirst($row['loan']->status) }}</td>
                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format((float) $row['loan']->pokok, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format($row['period_paid'] / 100, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format($row['principal_remaining'] / 100, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format($row['obligation_remaining'] / 100, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        Tidak ada pinjaman untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @else
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Jatuh tempo</th>
                                <th>Anggota</th>
                                <th>Nomor pinjaman</th>
                                <th>Angsuran</th>
                                <th class="text-end">Tagihan</th>
                                <th class="text-end">Dibayar periode ini</th>
                                <th class="text-end">Sisa saat ini</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['rows'] as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $row['installment']->jatuh_tempo->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        {{ $row['installment']->pinjaman->anggota?->nama
                                            ?? 'Anggota dihapus' }}
                                    </td>
                                    <td>{{ $row['installment']->pinjaman->nomor_pinjaman }}</td>
                                    <td>{{ $row['installment']->urutan }}</td>
                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format($row['due'] / 100, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format($row['period_paid'] / 100, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        Rp {{ number_format($row['outstanding'] / 100, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if ($row['outstanding'] === 0)
                                            Lunas
                                        @elseif ($row['installment']->jatuh_tempo->lt(today()))
                                            Terlambat
                                        @else
                                            Belum lunas
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        Tidak ada jadwal angsuran untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    @endif
                </table>
            </div>

            <footer class="small text-secondary mt-3 report-footer">
                {{ $report['count'] }} baris · Dicetak {{ $printedAt }} · Koperasi Simpan Pinjam
            </footer>
        </article>
    </section>

    <style>
        @media print {
            @page { size: landscape; margin: 12mm; }
            body { background: #fff !important; font-size: 9pt; }
            .app-shell, .app-main { display: block !important; }
            .sidebar, .topbar { display: none !important; }
            .page-content { padding: 0 !important; }
            .report-paper { border: 0 !important; box-shadow: none !important; }
            .report-heading { text-align: center; margin-bottom: 1rem; }
            .report-table-wrap { overflow: visible !important; }
            .report-table { font-size: 8pt; }
            .report-table thead { display: table-header-group; }
            .report-table tr { break-inside: avoid; }
            .report-totals { break-inside: avoid; }
        }
    </style>
</x-layouts.app>
