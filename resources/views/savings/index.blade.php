<x-layouts.app title="Simpanan | Koperasi">
    <section>
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h3 mb-1">
                    {{ auth()->user()->role === 'petugas' ? 'Setoran simpanan' : 'Simpanan saya' }}
                </h1>
                <p class="text-secondary mb-0">
                    Saldo dihitung dari transaksi simpanan yang tercatat.
                </p>
            </div>
            @if (auth()->user()->role === 'petugas')
                <a href="{{ route('petugas.simpanan.create') }}" class="btn btn-primary">
                    Catat setoran
                </a>
            @endif
        </div>

        @if (auth()->user()->role === 'anggota' && ! $member)
            <div class="alert alert-warning" role="alert">
                Akun ini belum terhubung dengan data anggota. Hubungi petugas koperasi.
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body p-3">
                        <div class="small text-secondary">Total saldo simpanan</div>
                        <div class="fs-4 fw-semibold mt-1">
                            Rp {{ number_format($totalBalance, 0, ',', '.') }}
                        </div>
                        <div class="small text-secondary mt-1">Seluruh jenis simpanan</div>
                    </div>
                </div>
            </div>
            @foreach ($balances as $balance)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100">
                        <div class="card-body p-3">
                            <div class="small text-secondary">
                                {{ $balance->nama }}
                                @if (! $balance->is_active)
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                @endif
                            </div>
                            <div class="fs-5 fw-semibold mt-1">
                                Rp {{ number_format((float) ($balance->saldo ?? 0), 0, ',', '.') }}
                            </div>
                            <div class="small text-secondary mt-1">Saldo jenis simpanan</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="GET" class="card mb-3">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    @if (auth()->user()->role === 'petugas')
                        <div class="col-12 col-md-6 col-xl-3">
                            <label for="anggota_id" class="form-label">Anggota</label>
                            <select id="anggota_id" name="anggota_id" class="form-select">
                                <option value="">Semua anggota</option>
                                @foreach ($members as $listedMember)
                                    <option
                                        value="{{ $listedMember->id }}"
                                        @selected(
                                            (string) request('anggota_id') === (string) $listedMember->id
                                        )
                                    >
                                        {{ $listedMember->nomor_anggota }} — {{ $listedMember->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-12 col-md-6 col-xl-3">
                        <label for="jenis_simpanan_id" class="form-label">Jenis simpanan</label>
                        <select id="jenis_simpanan_id" name="jenis_simpanan_id" class="form-select">
                            <option value="">Semua jenis</option>
                            @foreach ($types as $type)
                                <option
                                    value="{{ $type->id }}"
                                    @selected((string) request('jenis_simpanan_id') === (string) $type->id)
                                >
                                    {{ $type->nama }}{{ $type->is_active ? '' : ' (nonaktif)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <label for="dari" class="form-label">Dari tanggal</label>
                        <input
                            id="dari"
                            type="date"
                            name="dari"
                            value="{{ request('dari') }}"
                            class="form-control"
                        >
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <label for="sampai" class="form-label">Sampai tanggal</label>
                        <input
                            id="sampai"
                            type="date"
                            name="sampai"
                            value="{{ request('sampai') }}"
                            class="form-control"
                        >
                    </div>
                    <div class="col-12 col-md-4 col-xl-2 d-flex gap-2">
                        <button class="btn btn-outline-primary flex-grow-1">Filter</button>
                        <a
                            href="{{ auth()->user()->role === 'petugas'
                                ? route('petugas.simpanan.index')
                                : route('anggota.simpanan.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-header bg-white py-3">
                <h2 class="h5 mb-0">Riwayat setoran</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Nomor transaksi</th>
                            @if (auth()->user()->role === 'petugas')
                                <th scope="col">Anggota</th>
                            @endif
                            <th scope="col">Jenis</th>
                            <th scope="col">Periode</th>
                            <th scope="col" class="text-end">Nominal</th>
                            <th scope="col">Metode</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->tanggal_transaksi->format('d/m/Y') }}</td>
                                <td class="text-nowrap">{{ $transaction->nomor_transaksi }}</td>
                                @if (auth()->user()->role === 'petugas')
                                    <td>
                                        {{ $transaction->anggota?->nomor_anggota }}
                                        — {{ $transaction->anggota?->nama }}
                                    </td>
                                @endif
                                <td>
                                    {{ $transaction->jenis_simpanan_nama_snapshot
                                        ?? $transaction->jenis?->nama
                                        ?? 'Simpanan' }}
                                </td>
                                <td>{{ $transaction->periode ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format((float) $transaction->nominal, 0, ',', '.') }}
                                </td>
                                <td>{{ $transaction->metode ?? '—' }}</td>
                                <td>
                                    <span class="badge text-bg-{{
                                        $transaction->status === 'tercatat'
                                            ? 'success'
                                            : ($transaction->status === 'pembalikan' ? 'info' : 'secondary')
                                    }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <a
                                            href="{{ route(
                                                auth()->user()->role === 'petugas'
                                                    ? 'petugas.simpanan.receipt'
                                                    : 'anggota.simpanan.receipt',
                                                $transaction,
                                            ) }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Bukti
                                        </a>
                                        @if (
                                            auth()->user()->role === 'petugas'
                                            && $transaction->status === 'tercatat'
                                            && $transaction->pembalikan_dari_id === null
                                        )
                                            <details>
                                                <summary class="btn btn-sm btn-outline-danger">
                                                    Batalkan
                                                </summary>
                                                <form
                                                    method="POST"
                                                    action="{{ route('petugas.simpanan.cancel', $transaction) }}"
                                                    class="card card-body mt-2"
                                                >
                                                    @csrf
                                                    <label
                                                        for="alasan_koreksi_{{ $transaction->id }}"
                                                        class="form-label small"
                                                    >
                                                        Alasan pembatalan
                                                    </label>
                                                    <textarea
                                                        id="alasan_koreksi_{{ $transaction->id }}"
                                                        name="alasan_koreksi"
                                                        rows="2"
                                                        maxlength="2000"
                                                        class="form-control"
                                                        required
                                                    ></textarea>
                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm mt-2"
                                                    >
                                                        Konfirmasi pembatalan
                                                    </button>
                                                </form>
                                            </details>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ auth()->user()->role === 'petugas' ? 9 : 8 }}"
                                    class="py-4 text-center text-secondary"
                                >
                                    Belum ada transaksi simpanan yang cocok dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())
                <div class="card-body border-top p-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
