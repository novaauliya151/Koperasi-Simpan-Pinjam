<x-layouts.app title="Ubah anggota | Koperasi">
    <section class="mx-auto max-w-2xl space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">Ubah data anggota</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Status saat ini:
                        {{ str_replace('_', ' ', ucfirst($member->status_keanggotaan)) }}
                    </p>
                </div>
                <a
                    href="{{ route('petugas.anggota.index') }}"
                    class="text-sm font-medium text-blue-700 hover:underline"
                >
                    Kembali ke daftar
                </a>
            </div>
            <form
                method="POST"
                action="{{ route('petugas.anggota.update', $member) }}"
            >
                @csrf
                @method('PUT')
                @include('staff.members._form')
            </form>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Verifikasi keanggotaan</h2>
            <p class="mt-1 text-sm text-slate-600">
                Perubahan status tidak menghapus data anggota atau kewajiban yang sudah tercatat.
            </p>
            <form
                method="POST"
                action="{{ route('petugas.anggota.status', $member) }}"
                class="mt-4 flex flex-wrap gap-3"
            >
                @csrf
                @method('PATCH')
                <select
                    name="status_keanggotaan"
                    class="min-w-56 rounded-lg border border-slate-300 px-3 py-2"
                >
                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status }}"
                            @selected(old('status_keanggotaan', $member->status_keanggotaan) === $status)
                        >
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-blue-700 px-4 py-2 font-medium text-white">
                    Simpan status
                </button>
            </form>
            @if ($member->user)
                <p class="mt-3 text-sm text-slate-600">
                    Akun terkait: {{ $member->user->email }}
                    ({{ $member->user->is_active ? 'akses aktif' : 'akses nonaktif' }}).
                    Status keanggotaan dan status akun dikelola terpisah.
                </p>
            @endif
        </div>
    </section>
</x-layouts.app>
