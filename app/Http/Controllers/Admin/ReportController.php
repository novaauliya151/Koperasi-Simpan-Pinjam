<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlokasiPembayaran;
use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\JenisSimpanan;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\TransaksiSimpanan;
use App\Services\LoanScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly LoanScheduleService $scheduleService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'laporan' => ['nullable', Rule::in(['anggota', 'simpanan', 'pinjaman', 'angsuran'])],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'anggota_id' => ['nullable', 'integer', 'exists:anggotas,id'],
            'jenis_simpanan_id' => ['nullable', 'integer', 'exists:jenis_simpanans,id'],
            'status_keanggotaan' => [
                'nullable',
                Rule::in(['aktif', 'menunggu_verifikasi', 'tidak_aktif', 'keluar']),
            ],
            'status_pinjaman' => [
                'nullable',
                Rule::in(['aktif', 'lunas']),
            ],
        ]);
        $filters['sampai'] ??= today()->toDateString();
        $reportType = $filters['laporan'] ?? 'anggota';
        $report = match ($reportType) {
            'simpanan' => $this->savingsReport($filters),
            'pinjaman' => $this->loanReport($filters),
            'angsuran' => $this->installmentReport($filters),
            default => $this->memberReport($filters),
        };

        return view('admin.reports.index', [
            'reportType' => $reportType,
            'filters' => $filters,
            'report' => $report,
            'members' => Anggota::query()->orderBy('nama')->get(['id', 'nomor_anggota', 'nama']),
            'savingTypes' => JenisSimpanan::query()->orderBy('kode')->get(['id', 'kode', 'nama']),
            'printedAt' => now()->locale('id')->translatedFormat('d F Y H:i'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     title: string,
     *     description: string,
     *     rows: Collection<int, Anggota>,
     *     count: int,
     *     totals: array{}
     * }
     */
    private function memberReport(array $filters): array
    {
        $members = Anggota::query()
            ->when(
                $filters['status_keanggotaan'] ?? null,
                fn ($query, string $status) => $query->where('status_keanggotaan', $status),
            )
            ->when(
                $filters['dari'] ?? null,
                fn ($query, string $date) => $query->whereDate('tanggal_bergabung', '>=', $date),
            )
            ->when(
                $filters['sampai'] ?? null,
                fn ($query, string $date) => $query->whereDate('tanggal_bergabung', '<=', $date),
            )
            ->orderBy('nama')
            ->get();

        return [
            'title' => 'Laporan Anggota',
            'description' => 'Daftar anggota berdasarkan tanggal bergabung dan status keanggotaan.',
            'rows' => $members,
            'count' => $members->count(),
            'totals' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     title: string,
     *     description: string,
     *     rows: Collection<int, TransaksiSimpanan>,
     *     count: int,
     *     totals: array<string, string>
     * }
     */
    private function savingsReport(array $filters): array
    {
        $query = TransaksiSimpanan::query()
            ->with([
                'anggota:id,nomor_anggota,nama',
                'jenis:id,kode,nama',
                'pembatalan:id,status,pembalikan_dari_id',
            ])
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
            )
            ->orderBy('tanggal_transaksi')
            ->orderBy('id');

        $rows = $query->get();
        $periodNet = $rows->sum(fn (TransaksiSimpanan $transaction): int =>
            $this->savingMovementCents($transaction));
        $balanceQuery = TransaksiSimpanan::query()
            ->where(function ($query) use ($filters): void {
                $query->where('status', 'tercatat')
                    ->orWhere(function ($cancelled) use ($filters): void {
                        $cancelled->where('status', 'dibatalkan')
                            ->whereHas('pembatalan', function ($reversal) use ($filters): void {
                                if (isset($filters['sampai'])) {
                                    $reversal->whereDate('tanggal_transaksi', '>', $filters['sampai']);
                                }
                            });
                    });
            })
            ->when(
                $filters['anggota_id'] ?? null,
                fn ($query, int $id) => $query->where('anggota_id', $id),
            )
            ->when(
                $filters['jenis_simpanan_id'] ?? null,
                fn ($query, int $id) => $query->where('jenis_simpanan_id', $id),
            )
            ->when(
                $filters['sampai'] ?? null,
                fn ($query, string $date) => $query->whereDate('tanggal_transaksi', '<=', $date),
            );

        $balanceRows = $balanceQuery->get(['nominal']);
        $balanceAtEnd = $balanceRows->sum(fn (TransaksiSimpanan $transaction): int =>
            $this->toCents($transaction->nominal));

        return [
            'title' => 'Laporan Simpanan',
            'description' => 'Total neto periode = setoran bertanggal dalam periode dikurangi '
                .'pembalikan bertanggal dalam periode. Saldo posisi dihitung sampai tanggal akhir.',
            'rows' => $rows,
            'count' => $rows->count(),
            'totals' => [
                'Setoran periode' => $this->fromCents($rows->sum(
                    fn (TransaksiSimpanan $transaction): int => in_array(
                        $transaction->status,
                        ['tercatat', 'dibatalkan'],
                        true,
                    ) && (
                        $transaction->status === 'tercatat'
                        || $transaction->pembatalan?->status === 'pembalikan'
                    )
                        ? $this->toCents($transaction->nominal)
                        : 0,
                )),
                'Pembalikan periode' => $this->fromCents($rows
                    ->where('status', 'pembalikan')
                    ->sum(fn (TransaksiSimpanan $transaction): int => $this->toCents($transaction->nominal))),
                'Total neto periode' => $this->fromCents($periodNet),
                'Saldo posisi akhir' => $this->fromCents($balanceAtEnd),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     title: string,
     *     description: string,
     *     rows: Collection<int, array{
     *         loan: Pinjaman,
     *         principal_remaining: int,
     *         obligation_remaining: int,
     *         period_paid: int
     *     }>,
     *     count: int,
     *     totals: array<string, string>
     * }
     */
    private function loanReport(array $filters): array
    {
        $loans = Pinjaman::query()
            ->with([
                'anggota:id,nomor_anggota,nama',
                'angsuran.alokasi.pembayaran:id,status,tanggal_pembayaran',
                'pembayaran.pembatalan:id,status,tanggal_pembayaran,pembalikan_dari_id',
                'pembayaran' => fn ($query) => $query
                    ->whereIn('status', ['tercatat', 'dibatalkan', 'pembalikan'])
                    ->when(
                        $filters['dari'] ?? null,
                        fn ($query, string $date) => $query->whereDate('tanggal_pembayaran', '>=', $date),
                    )
                    ->when(
                        $filters['sampai'] ?? null,
                        fn ($query, string $date) => $query->whereDate('tanggal_pembayaran', '<=', $date),
                    ),
            ])
            ->when(
                $filters['anggota_id'] ?? null,
                fn ($query, int $id) => $query->where('anggota_id', $id),
            )
            ->when(
                $filters['status_pinjaman'] ?? null,
                fn ($query, string $status) => $query->where('status', $status),
            )
            ->orderBy('tanggal_pencairan')
            ->orderBy('id')
            ->get();
        $this->ensureSchedules($loans);

        $rows = $loans->map(function (Pinjaman $loan) use ($filters): array {
            $balances = $this->loanBalances($loan);

            return [
                'loan' => $loan,
                'principal_remaining' => $balances['principal_remaining'],
                'obligation_remaining' => $balances['obligation_remaining'],
                'period_paid' => $loan->pembayaran->sum(fn ($payment): int =>
                    $this->paymentMovementCents($payment, $filters)),
            ];
        });

        return [
            'title' => 'Laporan Pinjaman',
            'description' => 'Tanggal filter menentukan arus pembayaran. Pokok pinjaman mencakup '
                .'portofolio terfilter; sisa pokok dan kewajiban menunjukkan posisi saat ini.',
            'rows' => $rows,
            'count' => $rows->count(),
            'totals' => [
                'Pokok pinjaman terdaftar' => $this->fromCents($rows->sum(
                    fn (array $row): int => $this->toCents($row['loan']->pokok),
                )),
                'Pembayaran neto periode' => $this->fromCents($rows->sum('period_paid')),
                'Sisa pokok saat ini' => $this->fromCents($rows->sum('principal_remaining')),
                'Sisa kewajiban saat ini' => $this->fromCents($rows->sum('obligation_remaining')),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     title: string,
     *     description: string,
     *     rows: Collection<int, array{
     *         installment: Angsuran,
     *         due: int,
     *         period_paid: int,
     *         outstanding: int
     *     }>,
     *     count: int,
     *     totals: array<string, string>
     * }
     */
    private function installmentReport(array $filters): array
    {
        $scheduleLoans = Pinjaman::query()
            ->with('angsuran.alokasi.pembayaran:id,status,tanggal_pembayaran')
            ->when(
                $filters['anggota_id'] ?? null,
                fn ($query, int $id) => $query->where('anggota_id', $id),
            )
            ->when(
                $filters['status_pinjaman'] ?? null,
                fn ($query, string $status) => $query->where('status', $status),
            )
            ->get(['id', 'tenor_bulan']);
        $this->ensureSchedules($scheduleLoans);

        $installments = Angsuran::query()
            ->with([
                'pinjaman.anggota:id,nomor_anggota,nama',
                'alokasi.pembayaran:id,status,tanggal_pembayaran',
                'alokasi.pembayaran.pembatalan:id,status,tanggal_pembayaran,pembalikan_dari_id',
            ])
            ->whereIn('pinjaman_id', $scheduleLoans->modelKeys())
            ->whereHas('pinjaman', function ($query) use ($filters): void {
                $query
                    ->when(
                        $filters['anggota_id'] ?? null,
                        fn ($query, int $id) => $query->where('anggota_id', $id),
                    )
                    ->when(
                        $filters['status_pinjaman'] ?? null,
                        fn ($query, string $status) => $query->where('status', $status),
                    );
            })
            ->when(
                $filters['dari'] ?? null,
                fn ($query, string $date) => $query->whereDate('jatuh_tempo', '>=', $date),
            )
            ->when(
                $filters['sampai'] ?? null,
                fn ($query, string $date) => $query->whereDate('jatuh_tempo', '<=', $date),
            )
            ->orderBy('jatuh_tempo')
            ->orderBy('pinjaman_id')
            ->orderBy('urutan')
            ->get();
        $rows = $installments->map(function (Angsuran $installment) use ($filters): array {
            $validAllocations = $installment->alokasi->filter(
                fn ($allocation): bool => $allocation->pembayaran?->status === 'tercatat',
            );
            $paidCents = $validAllocations->sum(fn ($allocation): int =>
                $this->toCents($allocation->alokasi_pokok)
                + $this->toCents($allocation->alokasi_jasa));
            $periodPaidCents = $installment->alokasi->sum(
                fn ($allocation): int => $this->allocationMovementInPeriod(
                    $allocation,
                    $filters,
                ),
            );
            $dueCents = $this->toCents($installment->pokok_tagihan)
                + $this->toCents($installment->jasa_tagihan);

            return [
                'installment' => $installment,
                'due' => $dueCents,
                'period_paid' => $periodPaidCents,
                'outstanding' => max(0, $dueCents - $paidCents),
            ];
        });

        return [
            'title' => 'Laporan Angsuran',
            'description' => 'Tanggal filter menentukan jatuh tempo. Pembayaran neto hanya untuk '
                .'jadwal terfilter berdasarkan tanggal pembayaran; sisa tagihan menunjukkan posisi saat ini.',
            'rows' => $rows,
            'count' => $rows->count(),
            'totals' => [
                'Tagihan jatuh tempo periode' => $this->fromCents($rows->sum('due')),
                'Pembayaran neto periode' => $this->fromCents($rows->sum('period_paid')),
                'Sisa tagihan saat ini' => $this->fromCents($rows->sum('outstanding')),
            ],
        ];
    }

    /** @param Collection<int, Pinjaman> $loans */
    private function ensureSchedules(Collection $loans): void
    {
        foreach ($loans as $loan) {
            if ($loan->angsuran->count() !== $loan->tenor_bulan) {
                $this->scheduleService->ensure($loan);
                $loan->load('angsuran.alokasi.pembayaran:id,status,tanggal_pembayaran');
            }
        }
    }

    /**
     * @return array{principal_remaining: int, obligation_remaining: int}
     */
    private function loanBalances(Pinjaman $loan): array
    {
        $principalPaid = 0;
        $obligationRemaining = 0;

        foreach ($loan->angsuran as $installment) {
            $validAllocations = $installment->alokasi->filter(
                fn ($allocation): bool => $allocation->pembayaran?->status === 'tercatat',
            );
            $principalPaid += $validAllocations->sum(
                fn ($allocation): int => $this->toCents($allocation->alokasi_pokok),
            );
            $paid = $validAllocations->sum(fn ($allocation): int =>
                $this->toCents($allocation->alokasi_pokok)
                + $this->toCents($allocation->alokasi_jasa));
            $due = $this->toCents($installment->pokok_tagihan)
                + $this->toCents($installment->jasa_tagihan);
            $obligationRemaining += max(0, $due - $paid);
        }

        if ($loan->angsuran->isEmpty()) {
            $obligationRemaining = $this->toCents($loan->pokok);
        }

        return [
            'principal_remaining' => max(0, $this->toCents($loan->pokok) - $principalPaid),
            'obligation_remaining' => $obligationRemaining,
        ];
    }

    private function savingMovementCents(TransaksiSimpanan $transaction): int
    {
        return match ($transaction->status) {
            'tercatat' => $this->toCents($transaction->nominal),
            'dibatalkan' => $transaction->pembatalan?->status === 'pembalikan'
                ? $this->toCents($transaction->nominal)
                : 0,
            'pembalikan' => -$this->toCents($transaction->nominal),
            default => 0,
        };
    }

    /** @param array<string, mixed> $filters */
    private function allocationMovementInPeriod(AlokasiPembayaran $allocation, array $filters): int
    {
        $payment = $allocation->pembayaran;

        if (! $payment) {
            return 0;
        }

        $allocationCents = $this->toCents($allocation->alokasi_pokok)
            + $this->toCents($allocation->alokasi_jasa);
        $start = $filters['dari'] ?? null;
        $end = $filters['sampai'] ?? today()->toDateString();

        $movement = 0;
        $hasReversal = $payment->pembatalan?->status === 'pembalikan';

        if ($payment->status === 'tercatat' || ($payment->status === 'dibatalkan' && $hasReversal)) {
            $date = $payment->tanggal_pembayaran->toDateString();

            if ($this->isDateWithinPeriod($date, $start, $end)) {
                $movement += $allocationCents;
            }
        }

        $reversal = $payment->pembatalan;

        if ($hasReversal && $reversal) {
            $reversalDate = $reversal->tanggal_pembayaran->toDateString();

            if ($this->isDateWithinPeriod($reversalDate, $start, $end)) {
                $movement -= $allocationCents;
            }
        }

        return $movement;
    }

    /** @param array<string, mixed> $filters */
    private function paymentMovementCents(Pembayaran $payment, array $filters): int
    {
        $paymentDate = $payment->tanggal_pembayaran->toDateString();
        $start = $filters['dari'] ?? null;
        $end = $filters['sampai'] ?? today()->toDateString();
        $hasReversal = $payment->pembatalan?->status === 'pembalikan';
        $movement = 0;

        if (
            ($payment->status === 'tercatat' || ($payment->status === 'dibatalkan' && $hasReversal))
            && $this->isDateWithinPeriod($paymentDate, $start, $end)
        ) {
            $movement += $this->toCents($payment->nominal);
        }

        if ($payment->status === 'pembalikan' && $this->isDateWithinPeriod($paymentDate, $start, $end)) {
            $movement -= $this->toCents($payment->nominal);
        }

        return $movement;
    }

    private function isDateWithinPeriod(string $date, ?string $start, string $end): bool
    {
        return $date >= ($start ?? '0001-01-01') && $date <= $end;
    }

    private function toCents(string|int|float $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        $whole = intdiv(abs($amount), 100);
        $fraction = str_pad((string) (abs($amount) % 100), 2, '0', STR_PAD_LEFT);
        $sign = $amount < 0 ? '-' : '';

        return $sign.$whole.'.'.$fraction;
    }
}
