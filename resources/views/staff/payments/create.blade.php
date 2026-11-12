<x-layouts.app title="Catat pembayaran | Koperasi">
    <section class="mx-auto" style="max-width: 760px">
        <div class="mb-3">
            <a
                href="{{ route('petugas.pinjaman.show', $loan) }}"
                class="link-secondary text-decoration-none small"
            >
                ← Kembali ke detail pinjaman
            </a>
            <h1 class="h3 mt-2 mb-1">Catat pembayaran angsuran</h1>
            <p class="text-secondary mb-0">
                {{ $loan->nomor_pinjaman }} · {{ $loan->anggota?->nama }}
            </p>
        </div>

        @if ($installments->isEmpty())
            <div class="alert alert-info" role="status">
                Tidak ada tagihan yang dapat dibayar pada pinjaman ini.
            </div>
        @else
            <div class="alert alert-secondary" role="note">
                Untuk simulasi ini, pembayaran dicatat lunas per angsuran.
                Pembayaran sebagian belum menjadi aturan proyek.
            </div>

            <form
                method="POST"
                action="{{ route('petugas.pinjaman.pembayaran.store', $loan) }}"
                class="card"
            >
                @csrf
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="angsuran_id" class="form-label">Angsuran yang dibayar</label>
                        <select
                            id="angsuran_id"
                            name="angsuran_id"
                            class="form-select @error('angsuran_id') is-invalid @enderror"
                            required
                        >
                            @foreach ($installments as $item)
                                <option
                                    value="{{ $item['record']->id }}"
                                    @selected(
                                        old('angsuran_id', $installments->first()['record']->id)
                                            == $item['record']->id
                                    )
                                >
                                    Angsuran {{ $item['record']->urutan }} ·
                                    Jatuh tempo {{ $item['record']->jatuh_tempo->format('d/m/Y') }} ·
                                    Rp {{ number_format((float) $item['amount'], 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('angsuran_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Nominal akan mengikuti sisa tagihan penuh angsuran yang dipilih.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="tanggal_pembayaran" class="form-label">Tanggal pembayaran</label>
                            <input
                                id="tanggal_pembayaran"
                                name="tanggal_pembayaran"
                                type="date"
                                class="form-control @error('tanggal_pembayaran') is-invalid @enderror"
                                value="{{ old('tanggal_pembayaran', today()->toDateString()) }}"
                                max="{{ today()->toDateString() }}"
                                required
                            >
                            @error('tanggal_pembayaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="metode" class="form-label">Metode pembayaran</label>
                            <select
                                id="metode"
                                name="metode"
                                class="form-select @error('metode') is-invalid @enderror"
                                required
                            >
                                <option value="tunai" @selected(old('metode') === 'tunai')>Tunai</option>
                                <option value="transfer" @selected(old('metode') === 'transfer')>
                                    Transfer
                                </option>
                                <option value="lainnya" @selected(old('metode') === 'lainnya')>
                                    Lainnya
                                </option>
                            </select>
                            @error('metode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="referensi" class="form-label">Referensi (opsional)</label>
                        <input
                            id="referensi"
                            name="referensi"
                            type="text"
                            maxlength="100"
                            class="form-control @error('referensi') is-invalid @enderror"
                            value="{{ old('referensi') }}"
                            placeholder="Contoh: nomor bukti transfer"
                        >
                        @error('referensi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2 p-3">
                    <a
                        href="{{ route('petugas.pinjaman.show', $loan) }}"
                        class="btn btn-outline-secondary"
                    >
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">Simpan pembayaran</button>
                </div>
            </form>
        @endif
    </section>
</x-layouts.app>
