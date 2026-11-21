<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Koperasi Simpan Pinjam' }}</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    @if (! request()->routeIs('dashboard', '*.dashboard'))
        @vite('resources/css/legacy.css')
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    @auth
        <div class="app-shell">
            <aside
                class="sidebar offcanvas-lg offcanvas-start d-flex flex-column
                    {{ request()->routeIs(
                        'petugas.simpanan.receipt',
                        'anggota.simpanan.receipt',
                        'petugas.pembayaran.receipt',
                        'anggota.pembayaran.receipt',
                    )
                        ? 'd-print-none'
                        : '' }}"
                tabindex="-1"
                id="appSidebar"
                aria-labelledby="appSidebarLabel"
            >
                <div class="sidebar-heading">
                    <a href="{{ route('dashboard') }}" class="sidebar-brand" id="appSidebarLabel">
                        <span class="brand-mark">K</span>
                        <span>
                            <strong>Koperasi Simpan Pinjam</strong>
                            <small>PORTAL KOPERASI</small>
                        </span>
                    </a>
                    <button
                        type="button"
                        class="btn-close d-lg-none"
                        data-bs-dismiss="offcanvas"
                        aria-label="Tutup menu"
                    ></button>
                </div>

                <div class="offcanvas-body sidebar-content d-flex flex-column flex-grow-1">
                    <div
                        class="sidebar-navigation flex-grow-1"
                        role="region"
                        aria-label="Menu navigasi"
                        tabindex="0"
                    >
                        <div class="sidebar-section-label">MENU UTAMA</div>
                        <nav class="nav flex-column sidebar-nav" aria-label="Navigasi utama">
                            <a
                                class="nav-link
                                    {{ request()->routeIs('dashboard', '*.dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}"
                            >
                                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="3" y="3" width="8" height="8" rx="1" />
                                    <rect x="13" y="3" width="8" height="5" rx="1" />
                                    <rect x="13" y="10" width="8" height="11" rx="1" />
                                    <rect x="3" y="13" width="8" height="8" rx="1" />
                                </svg>
                                Dashboard
                            </a>

                            @if (auth()->user()->role === 'admin')
                                <div class="sidebar-section-label mt-4">DATABASE</div>
                                <a
                                    class="nav-link {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}"
                                    href="{{ route('admin.accounts.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="9" cy="8" r="3.5" />
                                        <path d="M2.5 20a6.5 6.5 0 0 1 13 0" />
                                        <path d="M16 5.5a3.5 3.5 0 0 1 0 6.8" />
                                        <path d="M18 14a5 5 0 0 1 3.5 4.8" />
                                    </svg>
                                    Kelola akun
                                </a>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('admin.saving-types.*') ? 'active' : '' }}"
                                    href="{{ route('admin.saving-types.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <ellipse cx="12" cy="6" rx="8" ry="3" />
                                        <path d="M4 6v5c0 1.7 3.6 3 8 3s8-1.3 8-3V6" />
                                        <path d="M4 11v5c0 1.7 3.6 3 8 3 1.5 0 2.9-.2 4-.6" />
                                        <path d="M17 16h4m-2-2v4" />
                                    </svg>
                                    Jenis simpanan
                                </a>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('admin.loan-terms.*') ? 'active' : '' }}"
                                    href="{{ route('admin.loan-terms.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M6 3h9l4 4v14H6z" />
                                        <path d="M14 3v5h5M9 12h7M9 16h7" />
                                    </svg>
                                    Ketentuan pinjaman
                                </a>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                                    href="{{ route('admin.reports.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 20V5M4 20h17" />
                                        <path d="m7 16 4-5 3 2 5-7" />
                                        <path d="M17 6h2v2" />
                                    </svg>
                                    Laporan
                                </a>
                                <div class="sidebar-section-label mt-4">AKTIVITAS</div>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}"
                                    href="{{ route('admin.activity-logs.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>
                                    Log aktivitas
                                </a>
                            @elseif (auth()->user()->role === 'petugas')
                                <div class="sidebar-section-label mt-4">DATABASE</div>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('petugas.anggota.*') ? 'active' : '' }}"
                                    href="{{ route('petugas.anggota.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="9" cy="8" r="3.5" />
                                        <path d="M2.5 20a6.5 6.5 0 0 1 13 0" />
                                        <path d="M16 5.5a3.5 3.5 0 0 1 0 6.8" />
                                        <path d="M18 14a5 5 0 0 1 3.5 4.8" />
                                    </svg>
                                    Data anggota
                                </a>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('petugas.simpanan.*') ? 'active' : '' }}"
                                    href="{{ route('petugas.simpanan.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="6" width="18" height="13" rx="2" />
                                        <circle cx="12" cy="12.5" r="2.5" />
                                        <path d="M7 9h.01M17 16h.01M12 2v3m-2-2 2 2 2-2" />
                                    </svg>
                                    Setoran simpanan
                                </a>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('petugas.pinjaman.*') ? 'active' : '' }}"
                                    href="{{ route('petugas.pinjaman.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <circle cx="12" cy="12" r="3" />
                                        <path d="M7 9h.01M17 15h.01" />
                                    </svg>
                                    Pinjaman
                                </a>
                            @elseif (auth()->user()->role === 'anggota')
                                <div class="sidebar-section-label mt-4">SIMPANAN</div>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('anggota.simpanan.*') ? 'active' : '' }}"
                                    href="{{ route('anggota.simpanan.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="6" width="18" height="13" rx="2" />
                                        <circle cx="12" cy="12.5" r="2.5" />
                                        <path d="M7 9h.01M17 16h.01M12 2v3m-2-2 2 2 2-2" />
                                    </svg>
                                    Simpanan saya
                                </a>
                                <a
                                    class="nav-link
                                        {{ request()->routeIs('anggota.pinjaman.*') ? 'active' : '' }}"
                                    href="{{ route('anggota.pinjaman.index') }}"
                                >
                                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <circle cx="12" cy="12" r="3" />
                                        <path d="M7 9h.01M17 15h.01" />
                                    </svg>
                                    Pinjaman saya
                                </a>
                            @endif
                        </nav>

                        <div class="sidebar-section-label mt-4">PENGATURAN</div>
                        <nav class="nav flex-column sidebar-nav" aria-label="Menu akun">
                            <a
                                class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                                href="{{ route('profile.edit') }}"
                            >
                                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path d="M4 21a8 8 0 0 1 16 0" />
                                </svg>
                                Profil saya
                            </a>
                        </nav>
                    </div>
                </div>

                <div class="sidebar-user">
                    <div class="user-avatar">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="user-summary">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-link sidebar-logout" title="Keluar">
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <main class="app-main">
                <header class="topbar
                    {{ request()->routeIs(
                        'petugas.simpanan.receipt',
                        'anggota.simpanan.receipt',
                        'petugas.pembayaran.receipt',
                        'anggota.pembayaran.receipt',
                    )
                        ? 'd-print-none'
                        : '' }}">
                    <button
                        class="btn btn-outline-secondary sidebar-toggle d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#appSidebar"
                        aria-controls="appSidebar"
                        aria-label="Buka menu"
                    >
                        ☰
                    </button>
                    <div class="topbar-breadcrumb">
                        <span>
                            {{ match (auth()->user()->role) {
                                'admin' => 'Administrasi',
                                'petugas' => 'Operasional',
                                default => 'Keanggotaan',
                            } }}
                        </span>
                        <span class="breadcrumb-divider" aria-hidden="true">/</span>
                        <strong>
                            {{ request()->routeIs('dashboard', '*.dashboard')
                                ? 'Dashboard'
                                : 'Portal koperasi' }}
                        </strong>
                    </div>
                    <a class="topbar-user" href="{{ route('profile.edit') }}">
                        <span class="topbar-user-copy">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>
                                {{ match (auth()->user()->role) {
                                    'admin' => 'Administrator',
                                    'petugas' => 'Petugas koperasi',
                                    default => 'Anggota',
                                } }}
                            </small>
                        </span>
                        <span class="topbar-user-caret" aria-hidden="true">⌄</span>
                    </a>
                    <div class="topbar-date d-none d-xl-block">
                        {{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </div>
                </header>

                <div class="container-fluid page-content">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                                aria-label="Tutup"
                            ></button>
                        </div>
                    @endif
                    {{ $slot }}
                </div>
            </main>
        </div>
    @else
        <main class="guest-shell">
            {{ $slot }}
        </main>
    @endauth

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>
</body>
</html>
