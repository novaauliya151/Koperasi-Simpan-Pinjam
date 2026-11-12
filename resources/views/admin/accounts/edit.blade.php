<x-layouts.app title="Ubah akun | Koperasi">
    <section class="mx-auto max-w-2xl rounded-xl bg-white p-6 shadow-sm">
        <h1 class="mb-6 text-2xl font-semibold text-slate-900">Ubah akun</h1>
        <form
            method="POST"
            action="{{ route('admin.accounts.update', $account) }}"
        >
            @csrf
            @method('PUT')
            @include('admin.accounts._form')
        </form>
    </section>
</x-layouts.app>
