<x-layouts.app title="Profil saya | Koperasi">
    <section class="mx-auto max-w-2xl rounded-xl bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold text-slate-900">Profil saya</h1>
        <p class="mt-2 text-sm text-slate-600">
            Ubah kontak dan kata sandi. Data identitas dikelola petugas.
        </p>

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-lg bg-rose-50 p-4 text-sm text-rose-800">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('profile.update') }}"
            class="mt-6 space-y-5"
        >
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium">Nama akun</label>
                <p class="rounded-lg bg-slate-50 px-3 py-2 text-slate-600">
                    {{ $user->name }}
                </p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Email login</label>
                <p class="rounded-lg bg-slate-50 px-3 py-2 text-slate-600">
                    {{ $user->email }}
                </p>
            </div>
            @if ($user->anggota)
                <div>
                    <label for="telepon" class="mb-1 block text-sm font-medium">
                        Nomor kontak
                    </label>
                    <input
                        id="telepon"
                        name="telepon"
                        value="{{ old('telepon', $user->anggota->telepon) }}"
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
                    >{{ old('alamat', $user->anggota->alamat) }}</textarea>
                </div>
            @endif
            <hr>
            <div>
                <label for="current_password" class="mb-1 block text-sm font-medium">
                    Kata sandi saat ini
                </label>
                <input
                    id="current_password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                >
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">
                    Kata sandi baru
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                >
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">
                    Konfirmasi kata sandi baru
                </label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                >
            </div>
            <button class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white">
                Simpan perubahan
            </button>
        </form>
    </section>
</x-layouts.app>
