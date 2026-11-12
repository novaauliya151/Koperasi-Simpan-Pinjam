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
        <label for="name" class="mb-1 block text-sm font-medium">Nama</label>
        <input
            id="name"
            name="name"
            value="{{ old('name', $account?->name) }}"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium">Email login</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $account?->email) }}"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="role" class="mb-1 block text-sm font-medium">Peran</label>
        <select id="role" name="role" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected(old('role', $account?->role ?? 'anggota') === $role)>
                    {{ ucfirst($role) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="anggota_id" class="mb-1 block text-sm font-medium">
            Data anggota
        </label>
        <select
            id="anggota_id"
            name="anggota_id"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
            <option value="">Tidak ditautkan</option>
            @foreach ($members as $member)
                <option
                    value="{{ $member->id }}"
                    @selected((string) old('anggota_id', $account?->anggota?->id) === (string) $member->id)
                >
                    {{ $member->nomor_anggota }} — {{ $member->nama }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">
            Wajib dipilih jika peran akun adalah anggota.
        </p>
    </div>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            @checked(old('is_active', $account?->is_active ?? true))
            class="rounded border-slate-300"
        >
        Akun aktif
    </label>


    <div>
        <label for="password" class="mb-1 block text-sm font-medium">
            Kata sandi {{ $account ? '(kosongkan jika tidak diubah)' : '' }}
        </label>
        <input
            id="password"
            name="password"
            type="password"
            @required(! $account)
            autocomplete="new-password"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-medium">
            Konfirmasi kata sandi
        </label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            @required(! $account)
            autocomplete="new-password"
            class="w-full rounded-lg border border-slate-300 px-3 py-2"
        >
    </div>

    <div class="flex gap-3">
        <button class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white">
            Simpan
        </button>
        <a href="{{ route('admin.accounts.index') }}" class="rounded-lg border px-4 py-2">
            Batal
        </a>
    </div>
</div>





