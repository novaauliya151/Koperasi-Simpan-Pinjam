<x-layouts.app title="Kelola akun | Koperasi">
    <section class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Kelola akun</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Cari akun dan filter berdasarkan peran atau status.
                </p>
            </div>
            <a
                href="{{ route('admin.accounts.create') }}"
                class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white"
            >
                Buat akun
            </a>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl bg-white p-4 shadow-sm sm:grid-cols-4">
            <input
                name="q"
                value="{{ request('q') }}"
                placeholder="Nama atau email"
                class="rounded-lg border border-slate-300 px-3 py-2"
            >
            <select name="role" class="rounded-lg border border-slate-300 px-3 py-2">
                <option value="">Semua peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>
                        {{ ucfirst($role) }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </select>
            <button class="rounded-lg border border-slate-300 px-3 py-2 font-medium">
                Terapkan filter
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Peran</th>
                        <th class="px-4 py-3 font-medium">Anggota</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($accounts as $account)
                        <tr>
                            <td class="px-4 py-3">{{ $account->name }}</td>
                            <td class="px-4 py-3">{{ $account->email }}</td>
                            <td class="px-4 py-3">{{ ucfirst($account->role) }}</td>
                            <td class="px-4 py-3">
                                {{ $account->anggota?->nomor_anggota ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                            </td>
                            <td class="px-4 py-3">
                                <a
                                    class="font-medium text-blue-700 hover:underline"
                                    href="{{ route('admin.accounts.edit', $account) }}"
                                >
                                    Ubah
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Tidak ada akun yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $accounts->links() }}
    </section>
</x-layouts.app>

