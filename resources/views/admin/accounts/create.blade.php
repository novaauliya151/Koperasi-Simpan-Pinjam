<x-layouts.app title="Buat akun | Koperasi">
    <section class="mx-auto max-w-2xl rounded-xl bg-white p-6 shadow-sm">
        <h1 class="mb-6 text-2xl font-semibold text-slate-900">Buat akun</h1>
        <form method="POST" action="{{ route('admin.accounts.store') }}">
            @csrf
            @include('admin.accounts._form', ['account' => null])
        </form>
    </section>
</x-layouts.app>
