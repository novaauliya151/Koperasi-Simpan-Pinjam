<x-layouts.app title="Jenis simpanan | Koperasi">
    <section class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Jenis simpanan</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Setiap perubahan nominal dibuat sebagai aturan baru dengan tanggal berlaku.
                </p>
            </div>
            <a
                href="{{ route('admin.saving-types.create') }}"
                class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white"
            >
                Tambah jenis
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Kode</th>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Ketentuan terbaru</th>
                        <th class="px-4 py-3 font-medium">Berlaku mulai</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($types as $type)
                        <tr>
                            <td class="px-4 py-3">{{ $type->kode }}</td>
                            <td class="px-4 py-3">{{ $type->nama }}</td>
                            <td class="px-4 py-3">
                                {{ $type->ketentuanTerbaru?->nominal_ketentuan === null
                                    ? 'Tidak ada nominal baku'
                                    : 'Rp '.number_format((float) $type->ketentuanTerbaru->nominal_ketentuan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $type->ketentuanTerbaru?->berlaku_mulai?->format('d-m-Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $type->is_active ? 'Aktif' : 'Nonaktif' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a
                                        href="{{ route('admin.saving-types.edit', $type) }}"
                                        class="font-medium text-blue-700 hover:underline"
                                    >
                                        Ubah
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.saving-types.status', $type) }}"
                                    >
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            type="hidden"
                                            name="is_active"
                                            value="{{ $type->is_active ? '0' : '1' }}"
                                        >
                                        <button class="font-medium text-slate-600 hover:underline">
                                            {{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Belum ada jenis simpanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $types->links() }}
    </section>
</x-layouts.app>
