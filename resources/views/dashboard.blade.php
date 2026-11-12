<x-layouts.app title="Dashboard | Koperasi">
    <section class="mb-8">
        <p class="text-sm font-medium text-blue-700">
            {{ ucfirst(auth()->user()->role) }}
        </p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
            Halo, {{ auth()->user()->name }}
        </h1>
        <p class="mt-3 text-slate-600">
            Akun aktif dan akses berdasarkan peran telah diterapkan.
        </p>
    </section>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-100"
        >
            Keluar
        </button>
    </form>
</x-layouts.app>
