<x-layouts.app title="Bukti pembayaran | Koperasi">
    <section class="col-12 col-lg-8 mx-auto">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
            <a
                href="{{ $staffView
                    ? route('petugas.pinjaman.show', $loan)
                    : route('anggota.pinjaman.show', $loan) }}"
                class="btn btn-outline-secondary"
            >
                Kembali ke pinjaman
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                Cetak bukti
            </button>
        </div>

        <article class="card">
            <header class="card-header bg-white p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">
                            Koperasi Simpan Pinjam
                        </div>
                        <h1 class="h3 mt-1 mb-0">
                            {{ $payment->status === 'pembalikan'
                                ? 'Bukti pembalikan pembayaran'
                                : 'Bukti pembayaran angsuran' }}
                        </h1>
                    </div>
                    <span
                        class="badge {{ $payment->status === 'tercatat'
                            ? 'text-bg-success'
                            : 'text-bg-secondary' }}"
                    >
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </header>

            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-secondary">Nomor pembayaran</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $payment->nomor_pembayaran }}</dd>

                    <dt class="col-sm-5 text-secondary">Anggota</dt>
                    <dd class="col-sm-7">
                        {{ $loan->anggota?->nomor_anggota }}
                        — {{ $loan->anggota?->nama }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Nomor pinjaman</dt>
                    <dd class="col-sm-7">{{ $loan->nomor_pinjaman }}</dd>

                    <dt class="col-sm-5 text-secondary">Tanggal pembayaran</dt>
                    <dd class="col-sm-7">
                        {{ $payment->tanggal_pembayaran
                            ->locale('id')
                            ->translatedFormat('d F Y') }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Angsuran</dt>
                    <dd class="col-sm-7">
                        {{ $allocationSource->alokasi
                            ->map(fn ($allocation) => $allocation->angsuran?->urutan)
                            ->filter()
                            ->map(fn ($number) => 'Angsuran '.$number)
                            ->join(', ') ?: '—' }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Metode</dt>
                    <dd class="col-sm-7">{{ ucfirst($payment->metode ?? '—') }}</dd>

                    <dt class="col-sm-5 text-secondary">Referensi</dt>
                    <dd class="col-sm-7">{{ $payment->referensi ?? '—' }}</dd>

                    <dt class="col-sm-5 text-secondary">Alokasi pokok</dt>
                    <dd class="col-sm-7">
                        Rp {{ number_format((float) $allocatedPrincipal, 0, ',', '.') }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Alokasi jasa</dt>
                    <dd class="col-sm-7">
                        Rp {{ number_format((float) $allocatedInterest, 0, ',', '.') }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Total pembayaran</dt>
                    <dd class="col-sm-7 fs-5 fw-semibold">
                        Rp {{ number_format((float) $payment->nominal, 0, ',', '.') }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Sisa kewajiban saat ini</dt>
                    <dd class="col-sm-7 fw-semibold">
                        Rp {{ number_format((float) $remainingObligation, 0, ',', '.') }}
                    </dd>
                </dl>

                @if ($payment->pembatalan)
                    <div class="alert alert-warning mt-3 mb-0">
                        Pembayaran ini dibatalkan melalui pembalikan
                        {{ $payment->pembatalan->nomor_pembayaran }}.
                        Alasan: {{ $payment->alasan_koreksi }}
                    </div>
                @elseif ($payment->pembalikan)
                    <div class="alert alert-info mt-3 mb-0">
                        Pembalikan dari pembayaran
                        {{ $payment->pembalikan->nomor_pembayaran }}.
                        Alasan: {{ $payment->alasan_koreksi }}
                    </div>
                @endif
            </div>

            <footer class="card-footer bg-white text-secondary small p-3">
                Sisa kewajiban menunjukkan kondisi pinjaman saat bukti ini dibuka.
            </footer>
        </article>
    </section>
</x-layouts.app>
