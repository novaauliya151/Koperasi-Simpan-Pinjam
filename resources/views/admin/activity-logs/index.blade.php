<x-layouts.app title="Log aktivitas | Koperasi">
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Log aktivitas</h1>
            <p class="mt-1 text-sm text-slate-600">
                Riwayat perubahan akun, anggota, dan pengaturan sistem.
            </p>
        </div>

        <form method="GET" class="grid gap-3 rounded-xl bg-white p-4 shadow-sm md:grid-cols-5">
            <input
                name="aksi"
                value="{{ request('aksi') }}"
                placeholder="Jenis aktivitas"
                class="rounded-lg border border-slate-300 px-3 py-2"
            >
            <select name="user_id" class="rounded-lg border border-slate-300 px-3 py-2">
                <option value="">Semua pengguna</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            <input
                type="date"
                name="dari"
                value="{{ request('dari') }}"
                class="rounded-lg border border-slate-300 px-3 py-2"
            >
            <input
                type="date"
                name="sampai"
                value="{{ request('sampai') }}"
                class="rounded-lg border border-slate-300 px-3 py-2"
            >
            <button class="rounded-lg border border-slate-300 px-3 py-2 font-medium">
                Terapkan filter
            </button>
        </form>

        <div class="space-y-3">
            @forelse ($logs as $log)
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap justify-between gap-2">
                        <div>
                            <h2 class="font-semibold text-slate-900">{{ $log->aksi }}</h2>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $log->user?->name ?? 'Akun dihapus' }}
                                · {{ $log->terjadi_pada->format('d-m-Y H:i') }}
                            </p>
                        </div>
                        <p class="text-sm text-slate-500">
                            {{ class_basename($log->subjek_tipe ?? '') }} #{{ $log->subjek_id ?? '—' }}
                        </p>
                    </div>
                    @if ($log->alasan)
                        <p class="mt-3 text-sm text-slate-700">Alasan: {{ $log->alasan }}</p>
                    @endif
                    @if ($log->perubahan)
                        <details class="mt-3">
                            <summary class="cursor-pointer text-sm font-medium text-blue-700">
                                Lihat perubahan
                            </summary>
                            <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-50 p-3 text-xs">{{ json_encode($log->perubahan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    @endif
                </article>
            @empty
                <p class="rounded-xl bg-white p-8 text-center text-slate-500">
                    Belum ada aktivitas yang cocok dengan filter.
                </p>
            @endforelse
        </div>

        {{ $logs->links() }}
    </section>
</x-layouts.app>
