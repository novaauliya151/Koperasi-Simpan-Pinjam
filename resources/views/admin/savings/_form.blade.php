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
        <label for="kode" class="mb-1 block text-sm font-medium">Kode</label>
        <input
            id="kode"
            name="kode"
            value="{{ old('kode', $type?->kode) }}"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="nama" class="mb-1 block text-sm font-medium">Nama jenis</label>
        <input
            id="nama"
            name="nama"
            value="{{ old('nama', $type?->nama) }}"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="nominal_ketentuan" class="mb-1 block text-sm font-medium">
            Nominal ketentuan (opsional)
        </label>
        <input
            id="nominal_ketentuan"
            name="nominal_ketentuan"
            type="number"
            min="0"
            step="0.01"
            value="{{ old('nominal_ketentuan', $type?->ketentuanTerbaru?->nominal_ketentuan) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="berlaku_mulai" class="mb-1 block text-sm font-medium">
            Berlaku mulai
        </label>
        <input
            id="berlaku_mulai"
            name="berlaku_mulai"
            type="date"
            value="{{ old('berlaku_mulai', $type?->ketentuanTerbaru?->berlaku_mulai?->format('Y-m-d') ?? now()->toDateString()) }}"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    @if ($type)
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-2 text-sm">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked(old('is_active', $type->is_active))
                class="rounded border-slate-300"
            >
            Jenis aktif untuk transaksi baru
        </label>
    @endif

    <div class="flex gap-3">
        <button class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white">
            Simpan
        </button>
        <a href="{{ route('admin.saving-types.index') }}" class="rounded-lg border px-4 py-2">
            Batal
        </a>
    </div>
</div>
