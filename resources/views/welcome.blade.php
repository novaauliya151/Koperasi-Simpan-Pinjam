<x-layouts.app title="Ringkasan Koperasi">
    <section class="mb-8">
        <p class="text-sm font-medium text-blue-700">Sistem Simpan Pinjam</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Fondasi aplikasi koperasi</h1>
        <p class="mt-3 max-w-2xl text-slate-600">Kelola anggota, simpanan, pinjaman, dan pembayaran dengan data transaksi yang dapat ditelusuri.</p>
    </section>
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-label="Modul aplikasi">
        @foreach ([['Anggota', 'Data dan verifikasi keanggotaan'], ['Simpanan', 'Setoran dan riwayat simpanan'], ['Pinjaman', 'Jadwal serta sisa kewajiban']] as [$label, $description])
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">{{ $label }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>
            </article>
        @endforeach
    </section>
</x-layouts.app>
