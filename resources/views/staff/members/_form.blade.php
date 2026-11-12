<div class="space-y-5">
    @if ($errors->any())
        <div role="alert" class="rounded-lg bg-rose-50 p-4 text-sm text-rose-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <label for="nomor_anggota" class="mb-1 block text-sm font-medium">
            Nomor anggota
        </label>
        <input
            id="nomor_anggota"
            name="nomor_anggota"
            value="{{ old('nomor_anggota', $member?->nomor_anggota) }}"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="nama" class="mb-1 block text-sm font-medium">Nama</label>
        <input
            id="nama"
            name="nama"
            value="{{ old('nama', $member?->nama) }}"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="nomor_identitas" class="mb-1 block text-sm font-medium">
            Nomor identitas (opsional)
        </label>
        <input
            id="nomor_identitas"
            name="nomor_identitas"
            value="{{ old('nomor_identitas', $member?->nomor_identitas) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="telepon" class="mb-1 block text-sm font-medium">Kontak</label>
        <input
            id="telepon"
            name="telepon"
            value="{{ old('telepon', $member?->telepon) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="alamat" class="mb-1 block text-sm font-medium">Alamat</label>
        <textarea
            id="alamat"
            name="alamat"
            rows="3"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >{{ old('alamat', $member?->alamat) }}</textarea>
    </div>

    <div>
        <label for="tanggal_bergabung" class="mb-1 block text-sm font-medium">
            Tanggal bergabung
        </label>
        <input
            id="tanggal_bergabung"
            name="tanggal_bergabung"
            type="date"
            value="{{ old('tanggal_bergabung', $member?->tanggal_bergabung?->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div class="flex gap-3">
        <button class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white">
            Simpan
        </button>
        <a href="{{ route('petugas.anggota.index') }}" class="rounded-lg border px-4 py-2">
            Batal
        </a>
    </div>
</div>
