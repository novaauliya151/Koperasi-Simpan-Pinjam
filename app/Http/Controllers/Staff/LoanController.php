<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\KetentuanPinjaman;
use App\Models\LogAktivitas;
use App\Models\Pinjaman;
use App\Services\LoanScheduleService;
use App\Services\InstallmentPaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function __construct(
        private readonly LoanScheduleService $scheduleService,
        private readonly InstallmentPaymentService $paymentService,
    ) {
    }

    public function staffIndex(): View
    {
        return view('loans.index', [
            'loans' => Pinjaman::query()
                ->with('anggota:id,nomor_anggota,nama')
                ->latest('tanggal_pencairan')
                ->latest('id')
                ->paginate(15),
            'staffView' => true,
        ]);
    }

    public function memberIndex(Request $request): View
    {
        $memberId = $request->user()->anggota?->id ?? 0;

        return view('loans.index', [
            'loans' => Pinjaman::query()
                ->with('anggota:id,nomor_anggota,nama')
                ->where('anggota_id', $memberId)
                ->latest('tanggal_pencairan')
                ->latest('id')
                ->paginate(15),
            'staffView' => false,
        ]);
    }

    public function staffShow(Pinjaman $loan): View
    {
        return $this->loanDetail($loan, true);
    }

    public function memberShow(Request $request, Pinjaman $loan): View
    {
        abort_unless(
            $request->user()->anggota?->id === $loan->anggota_id,
            404,
        );

        return $this->loanDetail($loan, false);
    }

    public function create(): View
    {
        $terms = $this->termsForDate(today()->toDateString());

        return view('staff.loans.create', [
            'members' => Anggota::query()
                ->where('status_keanggotaan', 'aktif')
                ->orderBy('nama')
                ->get(['id', 'nomor_anggota', 'nama']),
            'terms' => $terms,
            'loanNumber' => $this->nextLoanNumber(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nomor_pinjaman' => [
                'required',
                'string',
                'max:40',
                Rule::unique('pinjamans', 'nomor_pinjaman'),
            ],
            'anggota_id' => [
                'required',
                'integer',
                Rule::exists('anggotas', 'id')
                    ->where('status_keanggotaan', 'aktif'),
            ],
            'pokok' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:9999999999999.99',
            ],
            'tenor_bulan' => ['required', 'integer', 'min:1'],
            'tanggal_pencairan' => ['required', 'date'],
            'angsuran_pertama_jatuh_tempo' => [
                'required',
                'date',
                'after:tanggal_pencairan',
            ],
            'referensi_persetujuan' => ['required', 'string', 'max:100'],
            'referensi_pencairan' => ['required', 'string', 'max:100'],
        ]);

        $terms = $this->termsForDate($data['tanggal_pencairan']);
        Validator::make(
            ['tenor_bulan' => (string) $data['tenor_bulan']],
            ['tenor_bulan' => ['required', Rule::in(array_map('strval', $terms['tenor_bulan']))]],
        )->validate();

        try {
            DB::transaction(function () use ($data, $request, $terms): void {
                if (isset($terms['id'])) {
                    KetentuanPinjaman::query()
                        ->whereKey($terms['id'])
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                $member = Anggota::query()
                    ->whereKey($data['anggota_id'])
                    ->where('status_keanggotaan', 'aktif')
                    ->lockForUpdate()
                    ->first();

                if ($member === null) {
                    throw ValidationException::withMessages([
                        'anggota_id' => 'Pinjaman baru hanya dapat dicatat untuk anggota aktif.',
                    ]);
                }

                $loan = Pinjaman::create([
                    'nomor_pinjaman' => $data['nomor_pinjaman'],
                    'anggota_id' => $member->id,
                    'ketentuan_pinjaman_id' => $terms['id'] ?? null,
                    'pokok' => $data['pokok'],
                    'tenor_bulan' => $data['tenor_bulan'],
                    'tanggal_pencairan' => $data['tanggal_pencairan'],
                    'angsuran_pertama_jatuh_tempo' => $data['angsuran_pertama_jatuh_tempo'],
                    'referensi_persetujuan' => $data['referensi_persetujuan'],
                    'referensi_pencairan' => $data['referensi_pencairan'],
                    'jasa_flat_bulanan_snapshot' => $terms['jasa_flat_bulanan'],
                    'snapshot_ketentuan' => $this->termsSnapshot($terms),
                    'status' => 'aktif',
                    'dibuat_oleh' => $request->user()->id,
                ]);

                $this->scheduleService->ensureWithinTransaction($loan);

                LogAktivitas::create([
                    'user_id' => $request->user()->id,
                    'aksi' => 'loan.recorded',
                    'subjek_tipe' => Pinjaman::class,
                    'subjek_id' => $loan->id,
                    'perubahan' => [
                        'nomor_pinjaman' => ['new' => $loan->nomor_pinjaman],
                        'anggota_id' => ['new' => $member->id],
                        'pokok' => ['new' => $loan->pokok],
                        'tenor_bulan' => ['new' => $loan->tenor_bulan],
                    ],
                    'terjadi_pada' => now(),
                ]);
            });
        } catch (QueryException $exception) {
            $duplicate = Pinjaman::query()
                ->where('nomor_pinjaman', $data['nomor_pinjaman'])
                ->exists();

            if (! $duplicate) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'nomor_pinjaman' => 'Nomor pinjaman ini sudah digunakan.',
            ]);
        }

        return redirect()->route('petugas.pinjaman.index')
            ->with('status', 'Pinjaman yang telah disetujui berhasil dicatat.');
    }

    private function loanDetail(Pinjaman $loan, bool $staffView): View
    {
        $this->scheduleService->ensure($loan);
        $this->paymentService->refreshLoanState($loan);
        $loan->load([
            'anggota:id,nomor_anggota,nama',
            'ketentuan',
            'angsuran.alokasi.pembayaran:id,status',
            'pembayaran.alokasi.angsuran',
        ]);

        $principalPaidCents = 0;
        $unpaidTotalCents = 0;
        $installments = $loan->angsuran->sortBy('urutan')->map(
            function ($installment) use (&$principalPaidCents, &$unpaidTotalCents): array {
                $validAllocations = $installment->alokasi->filter(
                    fn ($allocation): bool => $allocation->pembayaran?->status === 'tercatat',
                );
                $paidPrincipalCents = $validAllocations->sum(
                    fn ($allocation): int => $this->toCents($allocation->alokasi_pokok),
                );
                $paidInterestCents = $validAllocations->sum(
                    fn ($allocation): int => $this->toCents($allocation->alokasi_jasa),
                );
                $principalDueCents = $this->toCents($installment->pokok_tagihan);
                $interestDueCents = $this->toCents($installment->jasa_tagihan);
                $outstandingCents = max(
                    0,
                    $principalDueCents + $interestDueCents
                        - $paidPrincipalCents - $paidInterestCents,
                );

                $principalPaidCents += $paidPrincipalCents;
                $unpaidTotalCents += $outstandingCents;

                return [
                    'record' => $installment,
                    'paid_principal' => $this->fromCents($paidPrincipalCents),
                    'paid_interest' => $this->fromCents($paidInterestCents),
                    'outstanding' => $this->fromCents($outstandingCents),
                ];
            },
        );

        $principalCents = $this->toCents($loan->pokok);
        $summary = [
            'principal' => $this->fromCents($principalCents),
            'principal_paid' => $this->fromCents(min($principalCents, $principalPaidCents)),
            'principal_remaining' => $this->fromCents(
                max(0, $principalCents - $principalPaidCents),
            ),
            'unpaid_total' => $this->fromCents($unpaidTotalCents),
            'total_obligation' => $this->fromCents(
                $installments->sum(fn (array $item): int =>
                    $this->toCents($item['record']->pokok_tagihan)
                    + $this->toCents($item['record']->jasa_tagihan)),
            ),
        ];
        $payments = $loan->pembayaran->sortByDesc('tanggal_pembayaran');
        $hasDueInstallments = $installments->contains(
            fn (array $item): bool => (float) $item['outstanding'] > 0
                && $item['record']->jatuh_tempo->lte(today()),
        );

        return view('loans.show', compact(
            'loan',
            'staffView',
            'installments',
            'summary',
            'payments',
            'hasDueInstallments',
        ));
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

    private function termsSnapshot(array $terms): array
    {
        return [
            'ketentuan_pinjaman_id' => $terms['id'] ?? null,
            'kode' => $terms['kode'],
            'nama' => $terms['nama'],
            'jenis_koperasi' => 'konvensional',
            'metode' => $terms['metode'],
            'jasa_flat_bulanan' => $terms['jasa_flat_bulanan'],
            'tenor_bulan' => $terms['tenor_bulan'],
            'pembulatan' => $terms['pembulatan'],
            'dasar' => $terms['dasar'],
            'sumber' => $terms['sumber'],
            'referensi' => $terms['referensi'],
            'berlaku_mulai' => $terms['berlaku_mulai'] ?? null,
            'catatan' => $terms['catatan'] ?? null,
        ];
    }

    private function termsForDate(string $date): array
    {
        $rule = KetentuanPinjaman::query()
            ->where('is_active', true)
            ->whereDate('berlaku_mulai', '<=', $date)
            ->orderByDesc('berlaku_mulai')
            ->orderByDesc('id')
            ->first();

        if ($rule === null) {
            return config('pinjaman.aturan');
        }

        return [
            'id' => $rule->id,
            'kode' => 'KETENTUAN-'.$rule->id,
            'nama' => $rule->nama,
            'metode' => $rule->metode_perhitungan ?? 'flat',
            'jasa_flat_bulanan' => $rule->jasa_flat_bulanan,
            'tenor_bulan' => $rule->tenor_bulan ?: config('pinjaman.aturan.tenor_bulan'),
            'pembulatan' => $rule->pembulatan ?? config('pinjaman.aturan.pembulatan'),
            'dasar' => $rule->dasar ?? config('pinjaman.aturan.dasar'),
            'sumber' => $rule->sumber ?? config('pinjaman.aturan.sumber'),
            'referensi' => config('pinjaman.aturan.referensi'),
            'berlaku_mulai' => $rule->berlaku_mulai?->toDateString(),
            'catatan' => $rule->keterangan,
        ];
    }

    private function nextLoanNumber(): string
    {
        do {
            $number = 'PNJ-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Pinjaman::query()->where('nomor_pinjaman', $number)->exists());

        return $number;
    }
}
