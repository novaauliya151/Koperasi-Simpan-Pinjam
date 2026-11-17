<?php

namespace App\Http\Controllers;

use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use App\Services\InstallmentPaymentService;
use App\Services\LoanScheduleService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanPaymentReceiptController extends Controller
{
    public function __construct(
        private readonly LoanScheduleService $scheduleService,
        private readonly InstallmentPaymentService $paymentService,
    ) {
    }

    public function staffReceipt(Pembayaran $payment): View
    {
        return $this->receipt($payment, true);
    }

    public function memberReceipt(Request $request, Pembayaran $payment): View
    {
        $payment->loadMissing('pinjaman');

        abort_unless(
            $request->user()->anggota?->id === $payment->pinjaman?->anggota_id,
            404,
        );

        return $this->receipt($payment, false);
    }

    private function receipt(Pembayaran $payment, bool $staffView): View
    {
        $payment->loadMissing('pinjaman');
        $loan = $payment->pinjaman;

        abort_unless($loan !== null, 404);

        $this->scheduleService->ensure($loan);
        $this->paymentService->refreshLoanState($loan);
        $payment->load([
            'pinjaman.anggota:id,nomor_anggota,nama',
            'alokasi.angsuran',
            'pembalikan.alokasi.angsuran',
            'pembatalan',
        ]);
        $loan->load('angsuran.alokasi.pembayaran:id,status');

        $allocationSource = $payment->pembalikan ?? $payment;
        $allocatedPrincipalCents = $allocationSource->alokasi->sum(
            fn (AlokasiPembayaran $allocation): int =>
                $this->toCents($allocation->alokasi_pokok),
        );
        $allocatedInterestCents = $allocationSource->alokasi->sum(
            fn (AlokasiPembayaran $allocation): int =>
                $this->toCents($allocation->alokasi_jasa),
        );
        $remainingObligationCents = 0;

        foreach ($loan->angsuran as $installment) {
            $validAllocations = $installment->alokasi->filter(
                fn (AlokasiPembayaran $allocation): bool =>
                    $allocation->pembayaran?->status === 'tercatat',
            );
            $paidCents = $validAllocations->sum(
                fn (AlokasiPembayaran $allocation): int =>
                    $this->toCents($allocation->alokasi_pokok)
                    + $this->toCents($allocation->alokasi_jasa),
            );
            $dueCents = $this->toCents($installment->pokok_tagihan)
                + $this->toCents($installment->jasa_tagihan);
            $remainingObligationCents += max(0, $dueCents - $paidCents);
        }

        return view('loans.payment-receipt', [
            'payment' => $payment,
            'loan' => $loan,
            'staffView' => $staffView,
            'allocationSource' => $allocationSource,
            'allocatedPrincipal' => $this->fromCents($allocatedPrincipalCents),
            'allocatedInterest' => $this->fromCents($allocatedInterestCents),
            'remainingObligation' => $this->fromCents($remainingObligationCents),
        ]);
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
