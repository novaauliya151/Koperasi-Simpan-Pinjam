<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\KetentuanPinjaman;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use App\Services\LoanScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly LoanScheduleService $scheduleService,
    ) {
    }

    public function __invoke(Request $request): View
    {
        $user = request()->user();

        $data = match ($user->role) {
            'admin' => $this->adminData(),
            'petugas' => $this->staffData($request),
            default => $this->memberData($user),
        };

        return view('dashboard', [
            'user' => $user,
            'data' => $data,
        ]);
    }

    private function adminData(): array
    {
        $sixMonthsAgo = now()->startOfMonth()->subMonths(5);
        $savings = TransaksiSimpanan::query()
            ->where('status', 'tercatat')
            ->whereDate('tanggal_transaksi', '>=', $sixMonthsAgo->toDateString())
            ->get(['tanggal_transaksi', 'nominal']);

        $monthlySavings = collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($savings): array {
                $month = now()->startOfMonth()->subMonths($monthsAgo);
                $monthKey = $month->format('Y-m');
                $amount = $savings
                    ->filter(fn (TransaksiSimpanan $saving): bool =>
                        $saving->tanggal_transaksi->format('Y-m') === $monthKey)
                    ->sum('nominal');

                return [
                    'label' => $month->locale('id')->translatedFormat('M'),
                    'amount' => (float) $amount,
                ];
            });
        $maximumSavings = max((float) $monthlySavings->max('amount'), 1);

        $monthlySavings = $monthlySavings->map(function (array $month) use ($maximumSavings): array {
            $month['height'] = $month['amount'] > 0
                ? max(8, (int) round($month['amount'] / $maximumSavings * 100))
                : 0;

            return $month;
        });

        $currentLoanTerms = KetentuanPinjaman::query()
            ->where('is_active', true)
            ->whereDate('berlaku_mulai', '<=', today())
            ->orderByDesc('berlaku_mulai')
            ->orderByDesc('id')
            ->first();
        $loanTerms = $currentLoanTerms
            ? [
                'metode' => $currentLoanTerms->metode_perhitungan ?? 'flat',
                'jasa_flat_bulanan' => $currentLoanTerms->jasa_flat_bulanan,
                'tenor_bulan' => $currentLoanTerms->tenor_bulan
                    ?: config('pinjaman.aturan.tenor_bulan'),
            ]
            : config('pinjaman.aturan');

        $activeLoans = Pinjaman::query()
            ->where('status', 'aktif')
            ->with('angsuran.alokasi.pembayaran:id,status')
            ->get(['id', 'pokok', 'tenor_bulan']);
        $this->ensureLoanSchedules($activeLoans);

        return [
            'accounts' => User::count(),
            'activeAccounts' => User::where('is_active', true)->count(),
            'members' => Anggota::count(),
            'activeMembers' => Anggota::where('status_keanggotaan', 'aktif')->count(),
            'pendingMembers' => Anggota::where(
                'status_keanggotaan',
                'menunggu_verifikasi',
            )->count(),
            'savingsTotal' => TransaksiSimpanan::where('status', 'tercatat')->sum('nominal'),
            'savingsTransactions' => TransaksiSimpanan::where('status', 'tercatat')->count(),
            'cancelledSavings' => TransaksiSimpanan::where('status', 'dibatalkan')->count(),
            'activeLoans' => $activeLoans->count(),
            'activeLoanTotal' => $this->fromCents($activeLoans->sum(
                fn (Pinjaman $loan): int => $this->loanBalances($loan)['principal_remaining'],
            )),
            'loanTerms' => $loanTerms,
            'monthlySavings' => $monthlySavings,
            'hasSavingsActivity' => $savings->isNotEmpty(),
            'recentActivity' => LogAktivitas::with('user:id,name')
                ->latest('terjadi_pada')
                ->limit(5)
                ->get(),
        ];
    }

    private function staffData(Request $request): array
    {
        $requestedPeriod = $request->query('periode');
        $isValidPeriod = is_string($requestedPeriod)
            && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $requestedPeriod) === 1;
        $selectedPeriod = $isValidPeriod
            ? $requestedPeriod
            : now()->format('Y-m');
        $periodStart = Carbon::createFromFormat('!Y-m', $selectedPeriod)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $dateRange = [
            $periodStart->toDateString(),
            $periodEnd->toDateString(),
        ];
        $activeLoans = Pinjaman::query()
            ->where('status', 'aktif')
            ->with('angsuran.alokasi.pembayaran:id,status')
            ->get(['id', 'pokok', 'tenor_bulan']);
        $this->ensureLoanSchedules($activeLoans);

        return [
            'selectedPeriod' => $selectedPeriod,
            'periodLabel' => $periodStart->locale('id')->translatedFormat('F Y'),
            'periodError' => $requestedPeriod !== null && ! $isValidPeriod
                ? 'Periode tidak valid. Pilih bulan dan tahun yang tersedia.'
                : null,
            'members' => Anggota::count(),
            'pendingMembers' => Anggota::where(
                'status_keanggotaan',
                'menunggu_verifikasi',
            )->count(),
            'activeMembers' => Anggota::where('status_keanggotaan', 'aktif')->count(),
            'savingsTotal' => TransaksiSimpanan::query()
                ->whereBetween('tanggal_transaksi', $dateRange)
                ->where('status', 'tercatat')
                ->sum('nominal'),
            'savingsTransactions' => TransaksiSimpanan::query()
                ->whereBetween('tanggal_transaksi', $dateRange)
                ->where('status', 'tercatat')
                ->count(),
            'reversedSavings' => TransaksiSimpanan::query()
                ->whereBetween('tanggal_transaksi', $dateRange)
                ->where('status', 'pembalikan')
                ->count(),
            'paymentTotal' => Pembayaran::query()
                ->whereBetween('tanggal_pembayaran', $dateRange)
                ->where('status', 'tercatat')
                ->sum('nominal'),
            'paymentCount' => Pembayaran::query()
                ->whereBetween('tanggal_pembayaran', $dateRange)
                ->where('status', 'tercatat')
                ->count(),
            'paymentReversals' => Pembayaran::query()
                ->whereBetween('tanggal_pembayaran', $dateRange)
                ->where('status', 'pembalikan')
                ->count(),
            'activeLoans' => $activeLoans->count(),
            'activeLoanTotal' => $this->fromCents($activeLoans->sum(
                fn (Pinjaman $loan): int => $this->loanBalances($loan)['principal_remaining'],
            )),
            'recentMembers' => Anggota::latest('created_at')->limit(5)->get(),
            'recentPayments' => Pembayaran::query()
                ->whereBetween('tanggal_pembayaran', $dateRange)
                ->with('pinjaman:id,nomor_pinjaman,anggota_id', 'pinjaman.anggota:id,nomor_anggota,nama')
                ->latest('tanggal_pembayaran')
                ->latest('id')
                ->limit(5)
                ->get(),
        ];
    }

    private function memberData(User $user): array
    {
        $member = $user->anggota;

        $memberLoans = $member
            ? $member->pinjaman()
                ->with('angsuran.alokasi.pembayaran:id,status')
                ->latest('tanggal_pencairan')
                ->get()
            : collect();
        $this->ensureLoanSchedules($memberLoans);
        $activeLoans = $memberLoans->where('status', 'aktif');
        $activeLoanPrincipal = 0;
        $activeLoanObligations = 0;
        $upcomingInstallments = collect();

        foreach ($memberLoans as $loan) {
            $balances = $this->loanBalances($loan);
            $loan->setAttribute(
                'principal_remaining_dashboard',
                $this->fromCents($balances['principal_remaining']),
            );
            $loan->setAttribute(
                'unpaid_total_dashboard',
                $this->fromCents($balances['unpaid_total']),
            );

            if ($loan->status !== 'aktif') {
                continue;
            }

            $activeLoanPrincipal += $balances['principal_remaining'];
            $activeLoanObligations += $balances['unpaid_total'];

            foreach ($loan->angsuran as $installment) {
                $outstandingCents = $this->installmentOutstandingCents($installment);

                if ($outstandingCents === 0) {
                    continue;
                }

                $upcomingInstallments->push([
                    'loan' => $loan,
                    'installment' => $installment,
                    'outstanding' => $this->fromCents($outstandingCents),
                    'status' => $installment->jatuh_tempo->lt(today())
                        ? 'Terlambat'
                        : 'Belum dibayar',
                ]);
            }
        }

        $savingsBalances = $member
            ? $member->simpanan()
                ->where('status', 'tercatat')
                ->select([
                    'jenis_simpanan_id',
                    'jenis_simpanan_kode_snapshot',
                    'jenis_simpanan_nama_snapshot',
                ])
                ->selectRaw('SUM(nominal) as saldo')
                ->groupBy([
                    'jenis_simpanan_id',
                    'jenis_simpanan_kode_snapshot',
                    'jenis_simpanan_nama_snapshot',
                ])
                ->with('jenis:id,kode,nama')
                ->get()
            : collect();

        return [
            'member' => $member,
            'savingsBalances' => $savingsBalances,
            'savingsTotal' => $member
                ? TransaksiSimpanan::where('anggota_id', $member->id)
                    ->where('status', 'tercatat')
                    ->sum('nominal')
                : 0,
            'savingsCount' => $member?->simpanan()->where('status', 'tercatat')->count() ?? 0,
            'cancelledSavings' => $member?->simpanan()->where('status', 'dibatalkan')->count() ?? 0,
            'activeLoans' => $activeLoans->count(),
            'activeLoanTotal' => $this->fromCents($activeLoanPrincipal),
            'activeLoanObligations' => $this->fromCents($activeLoanObligations),
            'recentLoans' => $memberLoans->take(3),
            'upcomingInstallments' => $upcomingInstallments
                ->sortBy(fn (array $item): string =>
                    $item['installment']->jatuh_tempo->toDateString())
                ->take(5)
                ->values(),
            'recentSavings' => $member
                ? $member->simpanan()
                    ->with('jenis')
                    ->latest('tanggal_transaksi')
                    ->limit(5)
                    ->get()
                : collect(),
            'recentPayments' => $member
                ? Pembayaran::query()
                    ->whereHas('pinjaman', fn ($query) =>
                        $query->where('anggota_id', $member->id))
                    ->with('pinjaman:id,nomor_pinjaman,anggota_id')
                    ->latest('tanggal_pembayaran')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                : collect(),
        ];
    }

    private function loanBalances(Pinjaman $loan): array
    {
        if (! $loan->relationLoaded('angsuran')) {
            $loan->load('angsuran.alokasi.pembayaran:id,status');
        }

        $principalPaid = 0;
        $unpaidTotal = 0;

        foreach ($loan->angsuran as $installment) {
            $allocations = $installment->alokasi->filter(
                fn ($allocation): bool => $allocation->pembayaran?->status === 'tercatat',
            );
            $principalPaid += $allocations->sum(
                fn ($allocation): int => $this->toCents($allocation->alokasi_pokok),
            );
            $unpaidTotal += $this->installmentOutstandingCents($installment);
        }

        if ($loan->angsuran->isEmpty()) {
            $unpaidTotal = $this->toCents($loan->pokok);
        }

        return [
            'principal_remaining' => max(0, $this->toCents($loan->pokok) - $principalPaid),
            'unpaid_total' => $unpaidTotal,
        ];
    }

    private function ensureLoanSchedules(iterable $loans): void
    {
        foreach ($loans as $loan) {
            if ($loan->angsuran->count() !== $loan->tenor_bulan) {
                $this->scheduleService->ensure($loan);
                $loan->load('angsuran.alokasi.pembayaran:id,status');
            }
        }
    }

    private function installmentOutstandingCents(Angsuran $installment): int
    {
        $allocations = $installment->alokasi->filter(
            fn ($allocation): bool => $allocation->pembayaran?->status === 'tercatat',
        );
        $paid = $allocations->sum(
            fn ($allocation): int => $this->toCents($allocation->alokasi_pokok)
                + $this->toCents($allocation->alokasi_jasa),
        );
        $due = $this->toCents($installment->pokok_tagihan)
            + $this->toCents($installment->jasa_tagihan);

        return max(0, $due - $paid);
    }

    private function toCents(string|int|float $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad(
            (string) ($amount % 100),
            2,
            '0',
            STR_PAD_LEFT,
        );
    }
}
