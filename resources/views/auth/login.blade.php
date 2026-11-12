<x-layouts.app title="Masuk | Koperasi">
    <section class="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold text-slate-900">Masuk</h1>
        <p class="mt-2 text-sm text-slate-600">
            Gunakan email dan kata sandi akun koperasi.
        </p>

        @if ($errors->any())
            <div
                role="alert"
                class="mt-5 rounded-lg bg-rose-50 p-4 text-sm text-rose-800"
            >
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="mt-6 space-y-5"
        >
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">
                    Email
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    required
                    autofocus
                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                >
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">
                    Kata sandi
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                >
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="rounded border-slate-300"
                >
                Ingat saya
            </label>

            <button
                type="submit"
                class="w-full rounded-lg bg-blue-700 px-4 py-2.5 font-medium text-white hover:bg-blue-800"
            >
                Masuk
            </button>
        </form>
    </section>
</x-layouts.app>
