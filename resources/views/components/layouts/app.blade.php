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
                class="sidebar offcanvas-lg offcanvas-start d-flex flex-column"
                tabindex="-1"
                id="appSidebar"
                aria-labelledby="appSidebarLabel"
            >
                <div class="sidebar-heading">
                    <a href="{{ route('dashboard') }}" class="sidebar-brand" id="appSidebarLabel">
                        <span class="brand-mark">K</span>
                        <span>
                            <strong>SIMPAN PINJAM</strong>
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
                                    <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z" />
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
                                        <path d="M12 3v18M5 8h14M5 16h14" />
                                        <circle cx="9" cy="8" r="2" />
                                        <circle cx="15" cy="16" r="2" />
                                    </svg>
                                    Jenis simpanan
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
                                    class="nav-link {{ request()->routeIs('petugas.anggota.*') ? 'active' : '' }}"
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
                <header class="topbar">
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
                    <div class="topbar-title">Sistem Informasi Koperasi</div>
                    <div class="topbar-date d-none d-sm-block">
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
