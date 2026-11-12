<x-layouts.app :title="'Detail pinjaman | Koperasi'">
    <section>
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <a
                    href="{{ $staffView
                        ? route('petugas.pinjaman.index')
                        : route('anggota.pinjaman.index') }}"
                    class="link-secondary text-decoration-none small"
                >
                    ← Kembali ke daftar pinjaman
                </a>
                <h1 class="h3 mt-2 mb-1">Detail pinjaman</h1>
                <p class="text-secondary mb-0">{{ $loan->nomor_pinjaman }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span
                    class="badge {{ $loan->status === 'lunas'
                        ? 'text-bg-primary'
                        : 'text-bg-success' }}"
                >
                    {{ ucfirst($loan->status) }}
                </span>
                @if ($staffView && $loan->status === 'aktif' && $hasDueInstallments)
                    <a
                        href="{{ route('petugas.pinjaman.pembayaran.create', $loan) }}"
                        class="btn btn-primary btn-sm"
                    >
                        Catat pembayaran
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Pokok awal</div>
                    <div class="h5 mb-0">
                        Rp {{ number_format((float) $summary['principal'], 0, ',', '.') }}
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Pokok terbayar</div>
                    <div class="h5 mb-0">
                        Rp {{ number_format((float) $summary['principal_paid'], 0, ',', '.') }}
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Sisa pokok</div>
                    <div class="h5 mb-0">
                        Rp {{ number_format((float) $summary['principal_remaining'], 0, ',', '.') }}
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Sisa seluruh kewajiban</div>
                    <div class="h5 mb-0">
                        Rp {{ number_format((float) $summary['unpaid_total'], 0, ',', '.') }}
                    </div>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-5">
                <section class="card h-100">
                    <div class="card-header bg-white"><h2 class="h6 mb-0">Informasi pinjaman</h2></div>
                    <dl class="row card-body mb-0">
                        @if ($staffView)
                            <dt class="col-sm-5 text-secondary fw-normal">Anggota</dt>
                            <dd class="col-sm-7">
                                {{ $loan->anggota?->nama }}
                                ({{ $loan->anggota?->nomor_anggota }})
                            </dd>
                        @endif
                        <dt class="col-sm-5 text-secondary fw-normal">Tenor</dt>
                        <dd class="col-sm-7">{{ $loan->tenor_bulan }} bulan</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Tanggal pencairan</dt>
                        <dd class="col-sm-7">{{ $loan->tanggal_pencairan->format('d/m/Y') }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Angsuran pertama</dt>
                        <dd class="col-sm-7">
                            {{ $loan->angsuran_pertama_jatuh_tempo->format('d/m/Y') }}
                        </dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Referensi persetujuan</dt>
                        <dd class="col-sm-7">{{ $loan->referensi_persetujuan }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Referensi pencairan</dt>
                        <dd class="col-sm-7">{{ $loan->referensi_pencairan }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Jasa flat</dt>
                        <dd class="col-sm-7">
                            {{ number_format(
                                (float) $loan->jasa_flat_bulanan_snapshot,
                                3,
                                ',',
                                '.',
                            ) }}% per bulan
                        </dd>
                    </dl>
                </section>
            </div>
            <div class="col-12 col-lg-7">
                <section class="card h-100">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Ketentuan yang tersimpan</h2>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            {{ $loan->snapshot_ketentuan['nama'] ?? 'Ketentuan pinjaman' }}
                        </p>
                        <p class="small text-secondary mb-2">
                            Perhitungan flat dengan jasa
                            {{ number_format(
                                (float) $loan->jasa_flat_bulanan_snapshot,
                                3,
                                ',',
                                '.',
                            ) }}% per bulan dari pokok awal. Angsuran pokok terakhir
                            menyesuaikan pembulatan agar jumlah pokok tepat.
                        </p>
                        <p class="small text-secondary mb-0">
                            Aturan ini merupakan asumsi simulasi proyek; penerapannya
                            perlu mengikuti keputusan koperasi.
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <section class="card">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between gap-2">
                <h2 class="h6 mb-0">Jadwal angsuran</h2>
                <span class="small text-secondary">
                    Total kewajiban: Rp {{ number_format(
                        (float) $summary['total_obligation'],
                        0,
                        ',',
                        '.',
                    ) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th><th>Jatuh tempo</th>
                            <th class="text-end">Pokok</th><th class="text-end">Jasa</th>
                            <th class="text-end">Sisa tagihan</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($installments as $installment)
                            <tr>
                                <td>{{ $installment['record']->urutan }}</td>
                                <td>{{ $installment['record']->jatuh_tempo->format('d/m/Y') }}</td>
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format(
                                        (float) $installment['record']->pokok_tagihan,
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </td>
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format(
                                        (float) $installment['record']->jasa_tagihan,
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </td>
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format(
                                        (float) $installment['outstanding'],
                                        0,
                                        ',',
                                        '.',
                                    ) }}
                                </td>
                                <td>
                                    @php
                                        $installmentStatus = match ($installment['record']->status) {
                                            'dibayar' => 'Lunas',
                                            'terlambat' => 'Terlambat',
                                            default => 'Belum dibayar',
                                        };
                                        $installmentBadge = match ($installment['record']->status) {
                                            'dibayar' => 'text-bg-success',
                                            'terlambat' => 'text-bg-danger',
                                            default => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $installmentBadge }}">
                                        {{ $installmentStatus }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card mt-3">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0">Riwayat pembayaran</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th><th>Nomor pembayaran</th><th>Angsuran</th>
                            <th>Metode</th><th class="text-end">Pokok</th>
                            <th class="text-end">Jasa</th><th class="text-end">Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            @php
                                $principalAllocation = $payment->alokasi->sum('alokasi_pokok');
                                $interestAllocation = $payment->alokasi->sum('alokasi_jasa');
                            @endphp
                            <tr>
                                <td>{{ $payment->tanggal_pembayaran->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ $payment->nomor_pembayaran }}</td>
                                <td>
                                    {{ $payment->alokasi
                                        ->map(fn ($allocation) => $allocation->angsuran?->urutan)
                                        ->filter()
                                        ->map(fn ($number) => 'Angsuran '.$number)
                                        ->join(', ') ?: '—' }}
                                </td>
                                <td>{{ ucfirst($payment->metode ?? '—') }}</td>
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format((float) $principalAllocation, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format((float) $interestAllocation, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format((float) $payment->nominal, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $payment->status === 'tercatat'
                                            ? 'text-bg-success'
                                            : 'text-bg-secondary' }}"
                                    >
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-4 text-center text-secondary">
                                    Belum ada pembayaran yang dicatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</x-layouts.app>
