<x-layouts.app title="Ketentuan pinjaman | Koperasi">
    <section>
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h3 mb-1">Ketentuan pinjaman</h1>
                <p class="text-secondary mb-0">
                    Aturan konvensional yang dipakai untuk pinjaman baru.
                </p>
            </div>
            @if ($currentRule)
                <span class="badge text-bg-success">Ketentuan tersimpan</span>
            @else
                <span class="badge text-bg-warning">Asumsi awal belum disimpan</span>
            @endif
        </div>

        <div class="alert alert-warning" role="alert">
            Tarif dan tenor ini adalah asumsi simulasi proyek. Membuat versi baru
            tidak mengubah snapshot pinjaman yang sudah tercatat.
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0">Ketentuan yang digunakan</h2>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5 text-secondary">Nama</dt>
                            <dd class="col-7">{{ $terms['nama'] }}</dd>
                            <dt class="col-5 text-secondary">Jenis koperasi</dt>
                            <dd class="col-7">Konvensional</dd>
                            <dt class="col-5 text-secondary">Metode</dt>
                            <dd class="col-7">{{ ucfirst($terms['metode']) }}</dd>
                            <dt class="col-5 text-secondary">Jasa per bulan</dt>
                            <dd class="col-7">
                                {{ number_format((float) $terms['jasa_flat_bulanan'], 2, ',', '.') }}%
                            </dd>
                            <dt class="col-5 text-secondary">Tenor</dt>
                            <dd class="col-7">{{ implode(', ', $terms['tenor_bulan']) }} bulan</dd>
                            <dt class="col-5 text-secondary">Pembulatan</dt>
                            <dd class="col-7">{{ $terms['pembulatan'] }}</dd>
                            <dt class="col-5 text-secondary">Berlaku mulai</dt>
                            <dd class="col-7">
                                {{ $currentRule?->berlaku_mulai?->format('d/m/Y') ?? 'Asumsi awal' }}
                            </dd>
                        </dl>
                    </div>
                    @if (! $currentRule)
                        <div class="card-footer bg-white small text-secondary">
                            Nilai awal dibaca dari konfigurasi prototipe sampai admin menyimpan versi.
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-7">
                <form method="POST" action="{{ route('admin.loan-terms.store') }}" class="card h-100">
                    @csrf
                    <div class="card-header bg-white py-3">
                        <h2 class="h5 mb-0">Buat versi ketentuan baru</h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="nama" class="form-label">Nama ketentuan</label>
                                <input
                                    id="nama"
                                    name="nama"
                                    value="{{ old('nama', $terms['nama']) }}"
                                    class="form-control"
                                    maxlength="255"
                                    required
                                >
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="jasa_flat_bulanan" class="form-label">
                                    Jasa flat per bulan (%)
                                </label>
                                <input
                                    id="jasa_flat_bulanan"
                                    name="jasa_flat_bulanan"
                                    type="number"
                                    value="{{ old('jasa_flat_bulanan', $terms['jasa_flat_bulanan']) }}"
                                    class="form-control"
                                    min="0.00001"
                                    step="0.00001"
                                    required
                                >
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="tenor_bulan" class="form-label">
                                    Pilihan tenor (bulan)
                                </label>
                                <input
                                    id="tenor_bulan"
                                    name="tenor_bulan"
                                    value="{{ old('tenor_bulan', implode(',', $terms['tenor_bulan'])) }}"
                                    class="form-control"
                                    placeholder="3,6,12"
                                    required
                                >
                                <div class="form-text">Pisahkan tiap tenor dengan koma.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="berlaku_mulai" class="form-label">Berlaku mulai</label>
                                <input
                                    id="berlaku_mulai"
                                    name="berlaku_mulai"
                                    type="date"
                                    value="{{ old('berlaku_mulai', $effectiveDateDefault) }}"
                                    class="form-control"
                                    min="{{ today()->toDateString() }}"
                                    required
                                >
                            </div>
                            <div class="col-12">
                                <label for="catatan" class="form-label">Catatan asumsi</label>
                                <textarea
                                    id="catatan"
                                    name="catatan"
                                    rows="2"
                                    maxlength="1000"
                                    class="form-control"
                                >{{ old('catatan') }}</textarea>
                            </div>
                            <div class="col-12 small text-secondary">
                                Metode perhitungan tetap flat untuk Day 10. Jadwal angsuran akan
                                dihitung setelah fungsi perhitungan dibuat pada Day 11.
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-end p-3">
                        <button type="submit" class="btn btn-primary">Simpan versi</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3">
                <h2 class="h5 mb-0">Riwayat ketentuan</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Berlaku mulai</th>
                            <th scope="col">Nama</th>
                            <th scope="col" class="text-end">Jasa/bulan</th>
                            <th scope="col">Tenor</th>
                            <th scope="col">Pinjaman tercatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $rule)
                            <tr>
                                <td>{{ $rule->berlaku_mulai?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $rule->nama }}</td>
                                <td class="text-end">
                                    {{ number_format((float) $rule->jasa_flat_bulanan, 2, ',', '.') }}%
                                </td>
                                <td>{{ implode(', ', $rule->tenor_bulan ?? []) }} bulan</td>
                                <td>{{ $rule->pinjaman_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-secondary">
                                    Belum ada versi yang disimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($history->hasPages())
                <div class="card-body border-top p-3">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
