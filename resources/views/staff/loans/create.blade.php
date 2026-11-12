<x-layouts.app title="Catat pinjaman | Koperasi">
    <section>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h3 mb-1">Catat pinjaman</h1>
                <p class="text-secondary mb-0">
                    Untuk pinjaman yang telah disetujui dan dicairkan di luar aplikasi.
                </p>
            </div>
            <a href="{{ route('petugas.pinjaman.index') }}" class="btn btn-outline-secondary">
                Kembali ke daftar
            </a>
        </div>

        <div class="alert alert-warning" role="alert">
            <div class="fw-semibold">Asumsi simulasi konvensional</div>
            <div>
                Metode {{ $terms['metode'] }}, jasa
                {{ number_format($terms['jasa_flat_bulanan'], 2, ',', '.') }}% per bulan,
                tenor {{ implode(', ', $terms['tenor_bulan']) }} bulan.
                Aturan ini untuk simulasi proyek, bukan kebijakan resmi koperasi.
                Jadwal angsuran dihitung pada tahap berikutnya.
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">Pinjaman belum dapat disimpan.</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('petugas.pinjaman.store') }}" class="card">
            @csrf
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label for="nomor_pinjaman" class="form-label">Nomor pinjaman</label>
                        <input
                            id="nomor_pinjaman"
                            name="nomor_pinjaman"
                            value="{{ old('nomor_pinjaman', $loanNumber) }}"
                            class="form-control"
                            maxlength="40"
                            readonly
                            required
                        >
                        @error('nomor_pinjaman')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-lg-6">
                        <label for="anggota_id" class="form-label">Anggota aktif</label>
                        <select id="anggota_id" name="anggota_id" class="form-select" required>
                            <option value="">Pilih anggota</option>
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

                    <div class="col-12 col-md-6">
                        <label for="pokok" class="form-label">Pokok pinjaman</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                id="pokok"
                                type="number"
                                name="pokok"
                                value="{{ old('pokok') }}"
                                class="form-control"
                                min="0.01"
                                max="9999999999999.99"
                                step="0.01"
                                required
                            >
                        </div>
                        @error('pokok')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tenor_bulan" class="form-label">Tenor</label>
                        <select id="tenor_bulan" name="tenor_bulan" class="form-select" required>
                            <option value="">Pilih tenor</option>
                            @foreach ($terms['tenor_bulan'] as $months)
                                <option
                                    value="{{ $months }}"
                                    @selected((string) old('tenor_bulan') === (string) $months)
                                >
                                    {{ $months }} bulan
                                </option>
                            @endforeach
                        </select>
                        @error('tenor_bulan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tanggal_pencairan" class="form-label">Tanggal pencairan</label>
                        <input
                            id="tanggal_pencairan"
                            type="date"
                            name="tanggal_pencairan"
                            value="{{ old('tanggal_pencairan') }}"
                            class="form-control"
                            required
                        >
                        @error('tanggal_pencairan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label
                            for="angsuran_pertama_jatuh_tempo"
                            class="form-label"
                        >
                            Jatuh tempo angsuran pertama
                        </label>
                        <input
                            id="angsuran_pertama_jatuh_tempo"
                            type="date"
                            name="angsuran_pertama_jatuh_tempo"
                            value="{{ old('angsuran_pertama_jatuh_tempo') }}"
                            class="form-control"
                            required
                        >
                        @error('angsuran_pertama_jatuh_tempo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="referensi_persetujuan" class="form-label">
                            Referensi persetujuan
                        </label>
                        <input
                            id="referensi_persetujuan"
                            name="referensi_persetujuan"
                            value="{{ old('referensi_persetujuan') }}"
                            class="form-control"
                            maxlength="100"
                            required
                        >
                        @error('referensi_persetujuan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="referensi_pencairan" class="form-label">
                            Referensi pencairan
                        </label>
                        <input
                            id="referensi_pencairan"
                            name="referensi_pencairan"
                            value="{{ old('referensi_pencairan') }}"
                            class="form-control"
                            maxlength="100"
                            required
                        >
                        @error('referensi_pencairan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2 p-3">
                <a href="{{ route('petugas.pinjaman.index') }}" class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">Simpan pinjaman</button>
            </div>
        </form>
    </section>
</x-layouts.app>
