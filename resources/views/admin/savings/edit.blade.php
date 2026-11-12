<x-layouts.app title="Ubah jenis simpanan | Koperasi">
    <section class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h1 class="mb-2 text-2xl font-semibold text-slate-900">
                Ubah {{ $type->nama }}
            </h1>
            <p class="mb-6 text-sm text-slate-600">
                Perubahan ketentuan dibuat sebagai versi baru dengan tanggal berlaku.
            </p>
            <form
                method="POST"
                action="{{ route('admin.saving-types.update', $type) }}"
            >
                @csrf
                @method('PUT')
                @include('admin.savings._form')
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <div class="p-5">
                <h2 class="text-lg font-semibold text-slate-900">Riwayat ketentuan</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Versi lama dipertahankan untuk transaksi historis.
                </p>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Berlaku mulai</th>
                        <th class="px-4 py-3 font-medium">Nominal ketentuan</th>
                        <th class="px-4 py-3 font-medium">Dibuat oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($history as $rule)
                        <tr>
                            <td class="px-4 py-3">{{ $rule->berlaku_mulai->format('d-m-Y') }}</td>
                            <td class="px-4 py-3">
                                {{ $rule->nominal_ketentuan === null
                                    ? 'Tidak ada nominal baku'
                                    : 'Rp '.number_format((float) $rule->nominal_ketentuan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">{{ $rule->pembuat?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-500">
                                Belum ada versi ketentuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $history->links() }}</div>
        </div>
    </section>
</x-layouts.app>
