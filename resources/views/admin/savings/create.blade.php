<x-layouts.app title="Tambah jenis simpanan | Koperasi">
    <section class="mx-auto max-w-2xl rounded-xl bg-white p-6 shadow-sm">
        <h1 class="mb-2 text-2xl font-semibold text-slate-900">Tambah jenis simpanan</h1>
        <p class="mb-6 text-sm text-slate-600">
            Aturan yang disimpan tidak mengubah versi sebelumnya.
        </p>
        <form method="POST" action="{{ route('admin.saving-types.store') }}">
            @csrf
            @include('admin.savings._form', ['type' => null])
        </form>
    </section>
</x-layouts.app>
