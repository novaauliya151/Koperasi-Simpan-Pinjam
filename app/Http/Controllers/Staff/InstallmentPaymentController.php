<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AlokasiPembayaran;
use App\Models\Pinjaman;
use App\Services\InstallmentPaymentService;
use App\Services\LoanScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallmentPaymentController extends Controller
{
    public function __construct(
        private readonly InstallmentPaymentService $paymentService,
        private readonly LoanScheduleService $scheduleService,
    ) {
    }

    public function create(Pinjaman $loan): View
    {
        $this->scheduleService->ensure($loan);
        $this->paymentService->refreshLoanState($loan);
        $loan->refresh();
        abort_unless($loan->status === 'aktif', 404);

        $loan->load('anggota:id,nomor_anggota,nama', 'angsuran.alokasi.pembayaran:id,status');
        $installments = $loan->angsuran
            ->sortBy('urutan')
            ->map(function ($installment): array {
                $allocations = $installment->alokasi->filter(
                    fn (AlokasiPembayaran $allocation): bool =>
                        $allocation->pembayaran?->status === 'tercatat',
                );
                $remainingPrincipal = max(
                    0,
                    $this->toCents($installment->pokok_tagihan)
                        - $allocations->sum(fn (AlokasiPembayaran $allocation): int =>
                            $this->toCents($allocation->alokasi_pokok)),
                );
                $remainingInterest = max(
                    0,
                    $this->toCents($installment->jasa_tagihan)
                        - $allocations->sum(fn (AlokasiPembayaran $allocation): int =>
                            $this->toCents($allocation->alokasi_jasa)),
                );

                return [
                    'record' => $installment,
                    'amount' => $this->fromCents($remainingPrincipal + $remainingInterest),
                    'amount_cents' => $remainingPrincipal + $remainingInterest,
                ];
            })
            ->filter(fn (array $item): bool => $item['amount_cents'] > 0)
            ->filter(fn (array $item): bool => $item['record']->jatuh_tempo->lte(today()))
            ->values();

        return view('staff.payments.create', compact('loan', 'installments'));
    }

    public function store(Request $request, Pinjaman $loan): RedirectResponse
    {
        $data = $request->validate([
            'angsuran_id' => ['required', 'integer'],
            'tanggal_pembayaran' => ['required', 'date', 'before_or_equal:today'],
            'metode' => ['required', 'string', 'in:tunai,transfer,lainnya'],
            'referensi' => ['nullable', 'string', 'max:100'],
        ]);

        $this->paymentService->recordFullInstallment(
            $loan,
            (int) $data['angsuran_id'],
            $data['tanggal_pembayaran'],
            $data['metode'],
            $data['referensi'] ?? null,
            (int) $request->user()->id,
        );

        return redirect()->route('petugas.pinjaman.show', $loan)
            ->with('status', 'Pembayaran angsuran berhasil dicatat.');
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
