<x-layouts.app :title="'Detail pinjaman | Koperasi'">
    <section>
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <a
                    href="{{ $staffView ? route('petugas.pinjaman.index') : route('anggota.pinjaman.index') }}"
                    class="link-secondary text-decoration-none small"
                >
                    ← Kembali ke daftar pinjaman
                </a>
                <h1 class="h3 mt-2 mb-1">Detail pinjaman</h1>
                <p class="text-secondary mb-0">{{ $loan->nomor_pinjaman }}</p>
            </div>
            <span class="badge text-bg-success">{{ ucfirst($loan->status) }}</span>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Pokok awal</div>
                    <div class="h5 mb-0">Rp {{ number_format((float) $summary['principal'], 0, ',', '.') }}</div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Pokok terbayar</div>
                    <div class="h5 mb-0">Rp {{ number_format((float) $summary['principal_paid'], 0, ',', '.') }}</div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Sisa pokok</div>
                    <div class="h5 mb-0">Rp {{ number_format((float) $summary['principal_remaining'], 0, ',', '.') }}</div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-secondary">Sisa seluruh kewajiban</div>
                    <div class="h5 mb-0">Rp {{ number_format((float) $summary['unpaid_total'], 0, ',', '.') }}</div>
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
                            <dd class="col-sm-7">{{ $loan->anggota?->nama }} ({{ $loan->anggota?->nomor_anggota }})</dd>
                        @endif
                        <dt class="col-sm-5 text-secondary fw-normal">Tenor</dt>
                        <dd class="col-sm-7">{{ $loan->tenor_bulan }} bulan</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Tanggal pencairan</dt>
                        <dd class="col-sm-7">{{ $loan->tanggal_pencairan->format('d/m/Y') }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Angsuran pertama</dt>
                        <dd class="col-sm-7">{{ $loan->angsuran_pertama_jatuh_tempo->format('d/m/Y') }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Referensi persetujuan</dt>
                        <dd class="col-sm-7">{{ $loan->referensi_persetujuan }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Referensi pencairan</dt>
                        <dd class="col-sm-7">{{ $loan->referensi_pencairan }}</dd>
                        <dt class="col-sm-5 text-secondary fw-normal">Jasa flat</dt>
                        <dd class="col-sm-7">{{ number_format((float) $loan->jasa_flat_bulanan_snapshot, 3, ',', '.') }}% per bulan</dd>
                    </dl>
                </section>
            </div>
            <div class="col-12 col-lg-7">
                <section class="card h-100">
                    <div class="card-header bg-white"><h2 class="h6 mb-0">Ketentuan yang tersimpan</h2></div>
                    <div class="card-body">
                        <p class="mb-2">{{ $loan->snapshot_ketentuan['nama'] ?? 'Ketentuan pinjaman' }}</p>
                        <p class="small text-secondary mb-2">
                            Perhitungan flat dengan jasa {{ number_format((float) $loan->jasa_flat_bulanan_snapshot, 3, ',', '.') }}% per bulan dari pokok awal. Angsuran pokok terakhir menyesuaikan pembulatan agar jumlah pokok tepat.
                        </p>
                        <p class="small text-secondary mb-0">
                            Aturan ini merupakan asumsi simulasi proyek; penerapannya perlu mengikuti keputusan koperasi.
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <section class="card">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between gap-2">
                <h2 class="h6 mb-0">Jadwal angsuran</h2>
                <span class="small text-secondary">Total kewajiban: Rp {{ number_format((float) $summary['total_obligation'], 0, ',', '.') }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th><th>Jatuh tempo</th>
                            <th class="text-end">Pokok</th><th class="text-end">Jasa</th>
                            <th class="text-end">Sisa tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($installments as $installment)
                            <tr>
                                <td>{{ $installment['record']->urutan }}</td>
                                <td>{{ $installment['record']->jatuh_tempo->format('d/m/Y') }}</td>
                                <td class="text-end text-nowrap">Rp {{ number_format((float) $installment['record']->pokok_tagihan, 0, ',', '.') }}</td>
                                <td class="text-end text-nowrap">Rp {{ number_format((float) $installment['record']->jasa_tagihan, 0, ',', '.') }}</td>
                                <td class="text-end text-nowrap">Rp {{ number_format((float) $installment['outstanding'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</x-layouts.app>
