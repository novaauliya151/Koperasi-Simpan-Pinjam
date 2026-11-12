<x-layouts.app title="Tambah anggota | Koperasi">
    <section class="mx-auto max-w-2xl rounded-xl bg-white p-6 shadow-sm">
        <h1 class="mb-2 text-2xl font-semibold text-slate-900">Tambah anggota</h1>
        <p class="mb-6 text-sm text-slate-600">
            Anggota baru akan dibuat dengan status menunggu verifikasi.
        </p>
        <form method="POST" action="{{ route('petugas.anggota.store') }}">
            @csrf
            @include('staff.members._form', ['member' => null])
        </form>
    </section>
</x-layouts.app>
