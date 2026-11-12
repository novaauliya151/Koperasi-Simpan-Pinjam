<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Koperasi Simpan Pinjam' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-4">
            <a class="text-lg font-semibold text-slate-900" href="{{ url('/') }}">
                Koperasi
            </a>
            <nav
                class="flex flex-wrap gap-4 text-sm text-slate-600"
                aria-label="Navigasi utama"
            >
                <a href="{{ url('/') }}" class="hover:text-blue-700">Ringkasan</a>
                @auth
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.accounts.index') }}" class="hover:text-blue-700">
                            Akun
                        </a>
                        <a href="#pengaturan" class="hover:text-blue-700">Pengaturan</a>
                        <a href="#laporan" class="hover:text-blue-700">Laporan</a>
                    @elseif (auth()->user()->role === 'petugas')
                        <a href="#anggota" class="hover:text-blue-700">Anggota</a>
                        <a href="#simpanan" class="hover:text-blue-700">Simpanan</a>
                        <a href="#pinjaman" class="hover:text-blue-700">Pinjaman</a>
                        <a href="#laporan" class="hover:text-blue-700">Laporan</a>
                    @else
                        <a href="#simpanan-saya" class="hover:text-blue-700">
                            Simpanan saya
                        </a>
                        <a href="#pinjaman-saya" class="hover:text-blue-700">
                            Pinjaman saya
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="hover:text-blue-700">
                        Profil
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-8">
        @if (session('status'))
            <p class="mb-6 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800">
                {{ session('status') }}
            </p>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
