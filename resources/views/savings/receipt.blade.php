<x-layouts.app title="Bukti simpanan | Koperasi">
    <section class="col-12 col-lg-8 mx-auto">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
            <a
                href="{{ $staffView ? route('petugas.simpanan.index') : route('anggota.simpanan.index') }}"
                class="btn btn-outline-secondary"
            >
                Kembali ke riwayat
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                Cetak bukti
            </button>
        </div>

        <article class="card">
            <div class="card-header bg-white p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Koperasi Simpan Pinjam</div>
                        <h1 class="h3 mt-1 mb-0">Bukti transaksi simpanan</h1>
                    </div>
                    <span class="badge text-bg-{{ $transaction->status === 'tercatat' ? 'success' : 'secondary' }}">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-5 text-secondary">Nomor transaksi</dt>
                    <dd class="col-sm-7 fw-semibold">{{ $transaction->nomor_transaksi }}</dd>

                    <dt class="col-sm-5 text-secondary">Anggota</dt>
                    <dd class="col-sm-7">
                        {{ $transaction->anggota?->nomor_anggota }}
                        — {{ $transaction->anggota?->nama }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Tanggal</dt>
                    <dd class="col-sm-7">
                        {{ $transaction->tanggal_transaksi->locale('id')->translatedFormat('d F Y') }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Jenis simpanan</dt>
                    <dd class="col-sm-7">
                        {{ $transaction->jenis_simpanan_nama_snapshot
                            ?? $transaction->jenis?->nama
                            ?? 'Simpanan' }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Periode</dt>
                    <dd class="col-sm-7">{{ $transaction->periode ?? '—' }}</dd>

                    <dt class="col-sm-5 text-secondary">Nominal</dt>
                    <dd class="col-sm-7 fs-5 fw-semibold">
                        Rp {{ number_format((float) $transaction->nominal, 0, ',', '.') }}
                    </dd>

                    <dt class="col-sm-5 text-secondary">Metode</dt>
                    <dd class="col-sm-7">{{ $transaction->metode ?? '—' }}</dd>

                    <dt class="col-sm-5 text-secondary">Dicatat oleh</dt>
                    <dd class="col-sm-7">{{ $transaction->petugas?->name ?? '—' }}</dd>
                </dl>

                @if ($transaction->pembalikan)
                    <div class="alert alert-info mt-3 mb-0">
                        Pembalikan dari transaksi
                        <a
                            href="{{ route(
                                $staffView ? 'petugas.simpanan.receipt' : 'anggota.simpanan.receipt',
                                $transaction->pembalikan,
                            ) }}"
                        >
                            {{ $transaction->pembalikan->nomor_transaksi }}
                        </a>.
                        Alasan: {{ $transaction->alasan_koreksi }}
                    </div>
                @elseif ($transaction->pembatalan)
                    <div class="alert alert-warning mt-3 mb-0">
                        Transaksi ini dibatalkan melalui
                        <a
                            href="{{ route(
                                $staffView ? 'petugas.simpanan.receipt' : 'anggota.simpanan.receipt',
                                $transaction->pembatalan,
                            ) }}"
                        >
                            {{ $transaction->pembatalan->nomor_transaksi }}
                        </a>.
                        Alasan: {{ $transaction->alasan_koreksi }}
                    </div>
                @elseif ($transaction->status === 'dibatalkan')
                    <div class="alert alert-warning mt-3 mb-0">
                        Transaksi ini dibatalkan. Alasan: {{ $transaction->alasan_koreksi }}
                    </div>
                @endif
            </div>

            <div class="card-footer bg-white text-secondary small p-3">
                Bukti ini dibuat dari catatan transaksi pada sistem koperasi.
            </div>
        </article>
    </section>
</x-layouts.app>
