<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\JenisSimpanan;
use App\Models\KetentuanSimpanan;
use App\Models\LogAktivitas;
use App\Models\TransaksiSimpanan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SavingsTransactionController extends Controller
{
    public function staffIndex(Request $request): View
    {
        $filters = $this->validateFilters($request, true);
        $memberId = $filters['anggota_id'] ?? null;
        $transactions = $this->transactionQuery($filters, $memberId)
            ->with('anggota:id,nomor_anggota,nama')
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('savings.index', [
            'transactions' => $transactions,
            'balances' => $this->balanceSummary($memberId),
            'totalBalance' => $this->totalBalance($memberId),
            'members' => Anggota::query()
                ->orderBy('nama')
                ->get(['id', 'nomor_anggota', 'nama']),
            'types' => $this->savingTypes(),
            'filters' => $filters,
            'member' => null,
        ]);
    }

    public function memberIndex(Request $request): View
    {
        $filters = $this->validateFilters($request, false);
        $member = $request->user()->anggota;
        $memberId = $member?->id ?? 0;
        $transactions = $this->transactionQuery($filters, $memberId)
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('savings.index', [
            'transactions' => $transactions,
            'balances' => $this->balanceSummary($memberId),
            'totalBalance' => $this->totalBalance($memberId),
            'members' => collect(),
            'types' => $this->savingTypes(),
            'filters' => $filters,
            'member' => $member,
        ]);
    }

    public function receipt(Request $request, TransaksiSimpanan $transaction): View
    {
        $transactionQuery = TransaksiSimpanan::query()
            ->with([
                'anggota:id,nomor_anggota,nama',
                'jenis:id,kode,nama',
                'petugas:id,name',
                'pembalikan:id,nomor_transaksi,status,alasan_koreksi',
                'pembatalan:id,nomor_transaksi,status,alasan_koreksi',
            ])
            ->whereKey($transaction->id);

        if ($request->user()->role === 'anggota') {
            $transactionQuery->where(
                'anggota_id',
                $request->user()->anggota?->id ?? 0,
            );
        }

        $transaction = $transactionQuery->firstOrFail();

        return view('savings.receipt', [
            'transaction' => $transaction,
            'staffView' => $request->user()->role === 'petugas',
        ]);
    }

    public function cancel(Request $request, TransaksiSimpanan $transaction): RedirectResponse
    {
        $data = $request->validate([
            'alasan_koreksi' => ['required', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($data, $request, $transaction): void {
                $original = TransaksiSimpanan::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $hasReversal = TransaksiSimpanan::query()
                    ->where('pembalikan_dari_id', $original->id)
                    ->exists();

                if (
                    $original->status !== 'tercatat'
                    || $original->pembalikan_dari_id !== null
                    || $hasReversal
                ) {
                    throw ValidationException::withMessages([
                        'transaksi' => 'Transaksi ini sudah dibatalkan atau tidak dapat dibatalkan.',
                    ]);
                }

                $original->update([
                    'status' => 'dibatalkan',
                    'alasan_koreksi' => $data['alasan_koreksi'],
                ]);

                $reversal = TransaksiSimpanan::create([
                    'nomor_transaksi' => $this->nextTransactionNumber(),
                    'anggota_id' => $original->anggota_id,
                    'jenis_simpanan_id' => $original->jenis_simpanan_id,
                    'ketentuan_simpanan_id' => $original->ketentuan_simpanan_id,
                    'jenis_simpanan_kode_snapshot' => $original->jenis_simpanan_kode_snapshot,
                    'jenis_simpanan_nama_snapshot' => $original->jenis_simpanan_nama_snapshot,
                    'nominal_ketentuan_snapshot' => $original->nominal_ketentuan_snapshot,
                    'tanggal_transaksi' => now()->toDateString(),
                    'periode' => $original->periode,
                    'nominal' => $original->nominal,
                    'metode' => $original->metode,
                    'keterangan' => 'Pembalikan dari '.$original->nomor_transaksi,
                    'status' => 'pembalikan',
                    'dibuat_oleh' => $request->user()->id,
                    'pembalikan_dari_id' => $original->id,
                    'alasan_koreksi' => $data['alasan_koreksi'],
                ]);

                LogAktivitas::create([
                    'user_id' => $request->user()->id,
                    'aksi' => 'savings.reversed',
                    'subjek_tipe' => TransaksiSimpanan::class,
                    'subjek_id' => $original->id,
                    'alasan' => $data['alasan_koreksi'],
                    'perubahan' => [
                        'status' => ['old' => 'tercatat', 'new' => 'dibatalkan'],
                        'pembalikan_id' => ['new' => $reversal->id],
                    ],
                    'terjadi_pada' => now(),
                ]);
            });
        } catch (QueryException $exception) {
            $alreadyReversed = TransaksiSimpanan::query()
                ->where('pembalikan_dari_id', $transaction->id)
                ->exists();

            if (! $alreadyReversed) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'transaksi' => 'Transaksi ini sudah dibatalkan.',
            ]);
        }

        return redirect()->route('petugas.simpanan.index')
            ->with('status', 'Transaksi simpanan berhasil dibatalkan dan tercatat di log aktivitas.');
    }

    public function create(): View
    {
        return view('staff.savings.create', [
            'members' => Anggota::query()
                ->where('status_keanggotaan', 'aktif')
                ->orderBy('nama')
                ->get(['id', 'nomor_anggota', 'nama']),
            'types' => JenisSimpanan::query()
                ->where('is_active', true)
                ->orderBy('kode')
                ->get(['id', 'kode', 'nama']),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'idempotency_key' => [
                'required',
                'uuid',
                Rule::unique('transaksi_simpanans', 'idempotency_key'),
            ],
            'anggota_id' => [
                'required',
                'integer',
                Rule::exists('anggotas', 'id')
                    ->where('status_keanggotaan', 'aktif'),
            ],
            'jenis_simpanan_id' => [
                'required',
                'integer',
                Rule::exists('jenis_simpanans', 'id')
                    ->where('is_active', true),
            ],
            'tanggal_transaksi' => ['required', 'date'],
            'periode' => [
                Rule::requiredIf(fn (): bool => JenisSimpanan::query()
                    ->whereKey($request->input('jenis_simpanan_id'))
                    ->where('kode', 'WAJIB')
                    ->exists()),
                'nullable',
                'date_format:Y-m',
            ],
            'nominal' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:9999999999999.99',
            ],
            'metode' => ['nullable', 'string', 'max:40'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($data, $request): void {
                $member = Anggota::query()
                    ->whereKey($data['anggota_id'])
                    ->where('status_keanggotaan', 'aktif')
                    ->lockForUpdate()
                    ->first();

                if ($member === null) {
                    throw ValidationException::withMessages([
                        'anggota_id' => 'Setoran hanya dapat dicatat untuk anggota aktif.',
                    ]);
                }

                $type = JenisSimpanan::query()
                    ->whereKey($data['jenis_simpanan_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if ($type === null) {
                    throw ValidationException::withMessages([
                        'jenis_simpanan_id' => 'Jenis simpanan ini sudah tidak aktif.',
                    ]);
                }

                $rule = $type->ketentuan()
                    ->whereDate('berlaku_mulai', '<=', $data['tanggal_transaksi'])
                    ->orderByDesc('berlaku_mulai')
                    ->lockForUpdate()
                    ->first();

                if ($rule === null) {
                    throw ValidationException::withMessages([
                        'jenis_simpanan_id' => 'Belum ada ketentuan yang berlaku pada tanggal setoran.',
                    ]);
                }

                $transaction = TransaksiSimpanan::create([
                    'nomor_transaksi' => $this->nextTransactionNumber(),
                    'idempotency_key' => $data['idempotency_key'],
                    'anggota_id' => $member->id,
                    'jenis_simpanan_id' => $type->id,
                    'ketentuan_simpanan_id' => $rule->id,
                    'jenis_simpanan_kode_snapshot' => $type->kode,
                    'jenis_simpanan_nama_snapshot' => $type->nama,
                    'nominal_ketentuan_snapshot' => $rule->nominal_ketentuan,
                    'tanggal_transaksi' => $data['tanggal_transaksi'],
                    'periode' => $data['periode'] ?? null,
                    'nominal' => $data['nominal'],
                    'metode' => $data['metode'] ?? null,
                    'keterangan' => $data['keterangan'] ?? null,
                    'status' => 'tercatat',
                    'dibuat_oleh' => $request->user()->id,
                ]);

                LogAktivitas::create([
                    'user_id' => $request->user()->id,
                    'aksi' => 'savings.recorded',
                    'subjek_tipe' => TransaksiSimpanan::class,
                    'subjek_id' => $transaction->id,
                    'perubahan' => [
                        'nomor_transaksi' => ['new' => $transaction->nomor_transaksi],
                        'anggota_id' => ['new' => $member->id],
                        'jenis_simpanan' => ['new' => $type->kode],
                        'nominal' => ['new' => $transaction->nominal],
                    ],
                    'terjadi_pada' => now(),
                ]);
            });
        } catch (QueryException $exception) {
            $duplicate = TransaksiSimpanan::query()
                ->where('idempotency_key', $data['idempotency_key'])
                ->exists();

            if (! $duplicate) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'idempotency_key' => 'Setoran ini sudah diterima sebelumnya.',
            ]);
        }

        return redirect()->route('petugas.simpanan.index')
            ->with('status', 'Setoran simpanan berhasil dicatat.');
    }

    private function validateFilters(Request $request, bool $includeMember): array
    {
        $rules = [
            'jenis_simpanan_id' => [
                'nullable',
                'integer',
                'exists:jenis_simpanans,id',
            ],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ];

        if ($includeMember) {
            $rules['anggota_id'] = ['nullable', 'integer', 'exists:anggotas,id'];
        }

        return $request->validate($rules);
    }

    private function transactionQuery(array $filters, ?int $memberId): \Illuminate\Database\Eloquent\Builder
    {
        return TransaksiSimpanan::query()
            ->with([
                'anggota:id,nomor_anggota,nama',
                'jenis:id,kode,nama',
                'petugas:id,name',
                'pembalikan:id,nomor_transaksi',
                'pembatalan:id,nomor_transaksi',
            ])
            ->when($memberId !== null, fn ($query) => $query->where('anggota_id', $memberId))
            ->when(
                $filters['anggota_id'] ?? null,
                fn ($query, int $id) => $query->where('anggota_id', $id),
            )
            ->when(
                $filters['jenis_simpanan_id'] ?? null,
                fn ($query, int $id) => $query->where('jenis_simpanan_id', $id),
            )
            ->when(
                $filters['dari'] ?? null,
                fn ($query, string $date) => $query->whereDate('tanggal_transaksi', '>=', $date),
            )
            ->when(
                $filters['sampai'] ?? null,
                fn ($query, string $date) => $query->whereDate('tanggal_transaksi', '<=', $date),
            );
    }

    private function balanceSummary(?int $memberId): \Illuminate\Support\Collection
    {
        return JenisSimpanan::query()
            ->withSum([
                'transaksi as saldo' => function ($query) use ($memberId): void {
                    $query->where('status', 'tercatat')
                        ->when(
                            $memberId !== null,
                            fn ($query) => $query->where('anggota_id', $memberId),
                        );
                },
            ], 'nominal')
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama', 'is_active']);
    }

    private function totalBalance(?int $memberId): float
    {
        return (float) TransaksiSimpanan::query()
            ->where('status', 'tercatat')
            ->when(
                $memberId !== null,
                fn ($query) => $query->where('anggota_id', $memberId),
            )
            ->sum('nominal');
    }

    private function savingTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return JenisSimpanan::query()
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama', 'is_active']);
    }

    private function nextTransactionNumber(): string
    {
        do {
            $number = 'SS-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (TransaksiSimpanan::where('nomor_transaksi', $number)->exists());

        return $number;
    }
}
