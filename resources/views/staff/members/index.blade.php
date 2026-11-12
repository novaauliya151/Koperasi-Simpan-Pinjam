<x-layouts.app title="Data anggota | Koperasi">
    <section class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Data anggota</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Cari anggota, periksa status, dan perbarui data keanggotaan.
                </p>
            </div>
            <a
                href="{{ route('petugas.anggota.create') }}"
                class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white"
            >
                Tambah anggota
            </a>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl bg-white p-4 shadow-sm sm:grid-cols-3">
            <input
                name="q"
                value="{{ request('q') }}"
                placeholder="Nama atau nomor anggota"
                class="rounded-lg border border-slate-300 px-3 py-2"
            >
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ str_replace('_', ' ', ucfirst($status)) }}
                    </option>
                @endforeach
            </select>
            <button class="rounded-lg border border-slate-300 px-3 py-2 font-medium">
                Terapkan filter
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nomor</th>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Kontak</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Akun</th>
                        <th class="px-4 py-3 font-medium">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($members as $member)
                        <tr>
                            <td class="px-4 py-3">{{ $member->nomor_anggota }}</td>
                            <td class="px-4 py-3">{{ $member->nama }}</td>
                            <td class="px-4 py-3">{{ $member->telepon ?? '—' }}</td>
                            <td class="px-4 py-3">
                                {{ str_replace('_', ' ', ucfirst($member->status_keanggotaan)) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $member->user?->email ?? 'Belum ada akun' }}
                            </td>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('petugas.anggota.edit', $member) }}"
                                    class="font-medium text-blue-700 hover:underline"
                                >
                                    Ubah
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Belum ada data anggota yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $members->links() }}
    </section>
</x-layouts.app>
