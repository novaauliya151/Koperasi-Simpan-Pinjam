<x-layouts.app title="Pinjaman | Koperasi">
    <section>
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h3 mb-1">
                    {{ $staffView ? 'Data pinjaman' : 'Pinjaman saya' }}
                </h1>
                <p class="text-secondary mb-0">
                    {{ $staffView
                        ? 'Catatan pinjaman yang sudah disetujui dan dicairkan.'
                        : 'Daftar pinjaman yang terhubung dengan akun Anda.' }}
                </p>
            </div>
            @if ($staffView)
                <a href="{{ route('petugas.pinjaman.create') }}" class="btn btn-primary">
                    Catat pinjaman
                </a>
            @endif
        </div>

        @if (! $staffView && ! auth()->user()->anggota)
            <div class="alert alert-warning" role="alert">
                Akun ini belum terhubung dengan data anggota. Hubungi petugas koperasi.
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nomor pinjaman</th>
                            @if ($staffView)
                                <th scope="col">Anggota</th>
                            @endif
                            <th scope="col" class="text-end">Pokok</th>
                            <th scope="col">Tenor</th>
                            <th scope="col">Pencairan</th>
                            <th scope="col">Angsuran pertama</th>
                            <th scope="col">Referensi</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            <tr>
                                <td class="text-nowrap fw-semibold">{{ $loan->nomor_pinjaman }}</td>
                                @if ($staffView)
                                    <td>
                                        {{ $loan->anggota?->nomor_anggota }}
                                        — {{ $loan->anggota?->nama }}
                                    </td>
                                @endif
                                <td class="text-end text-nowrap">
                                    Rp {{ number_format((float) $loan->pokok, 0, ',', '.') }}
                                </td>
                                <td>{{ $loan->tenor_bulan }} bulan</td>
                                <td>{{ $loan->tanggal_pencairan->format('d/m/Y') }}</td>
                                <td>
                                    {{ $loan->angsuran_pertama_jatuh_tempo->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div>{{ $loan->referensi_persetujuan }}</div>
                                    <small class="text-secondary">
                                        Cair: {{ $loan->referensi_pencairan ?? '—' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge text-bg-success">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a
                                        href="{{ $staffView
                                            ? route('petugas.pinjaman.show', $loan)
                                            : route('anggota.pinjaman.show', $loan) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="{{ $staffView ? 9 : 8 }}"
                                    class="py-4 text-center text-secondary"
                                >
                                    Belum ada pinjaman yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($loans->hasPages())
                <div class="card-body border-top p-3">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
