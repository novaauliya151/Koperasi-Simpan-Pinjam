<x-layouts.app title="Catat setoran | Koperasi">
    <section>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h3 mb-1">Catat setoran simpanan</h1>
                <p class="text-secondary mb-0">
                    Setoran hanya dapat dicatat untuk anggota aktif.
                </p>
            </div>
            <a href="{{ route('petugas.simpanan.index') }}" class="btn btn-outline-secondary">
                Kembali ke riwayat
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">Setoran belum dapat disimpan.</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('petugas.simpanan.store') }}" class="card">
            @csrf
            <input
                type="hidden"
                name="idempotency_key"
                value="{{ old('idempotency_key', $idempotencyKey) }}"
            >

            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label for="anggota_id" class="form-label">Anggota</label>
                        <select id="anggota_id" name="anggota_id" class="form-select" required>
                            <option value="">Pilih anggota aktif</option>
                            @foreach ($members as $member)
                                <option
                                    value="{{ $member->id }}"
                                    @selected((string) old('anggota_id') === (string) $member->id)
                                >
                                    {{ $member->nomor_anggota }} — {{ $member->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('anggota_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="jenis_simpanan_id" class="form-label">Jenis simpanan</label>
                        <select id="jenis_simpanan_id" name="jenis_simpanan_id" class="form-select" required>
                            <option value="">Pilih jenis simpanan aktif</option>
                            @foreach ($types as $type)
                                <option
                                    value="{{ $type->id }}"
                                    @selected((string) old('jenis_simpanan_id') === (string) $type->id)
                                >
                                    {{ $type->kode }} — {{ $type->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_simpanan_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tanggal_transaksi" class="form-label">Tanggal setoran</label>
                        <input
                            id="tanggal_transaksi"
                            type="date"
                            name="tanggal_transaksi"
                            value="{{ old('tanggal_transaksi', now()->toDateString()) }}"
                            class="form-control"
                            required
                        >
                        @error('tanggal_transaksi')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="periode" class="form-label">Periode simpanan wajib</label>
                        <input
                            id="periode"
                            type="month"
                            name="periode"
                            value="{{ old('periode') }}"
                            class="form-control"
                            aria-describedby="periode_help"
                        >
                        <div id="periode_help" class="form-text">
                            Diisi untuk setoran jenis WAJIB.
                        </div>
                        @error('periode')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="nominal" class="form-label">Nominal setoran</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                id="nominal"
                                type="number"
                                name="nominal"
                                value="{{ old('nominal') }}"
                                class="form-control"
                                min="0.01"
                                max="9999999999999.99"
                                step="0.01"
                                required
                            >
                        </div>
                        @error('nominal')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="metode" class="form-label">Metode setoran</label>
                        <input
                            id="metode"
                            type="text"
                            name="metode"
                            value="{{ old('metode') }}"
                            class="form-control"
                            maxlength="40"
                            placeholder="Contoh: tunai atau transfer"
                        >
                        @error('metode')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea
                            id="keterangan"
                            name="keterangan"
                            rows="3"
                            maxlength="2000"
                            class="form-control"
                        >{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex flex-wrap justify-content-end gap-2 p-3">
                <a href="{{ route('petugas.simpanan.index') }}" class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">Simpan setoran</button>
            </div>
        </form>
    </section>
</x-layouts.app>
