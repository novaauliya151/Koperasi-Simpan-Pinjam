<?php

namespace App\Services;

use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentReversalService
{
    public function __construct(
        private readonly InstallmentPaymentService $paymentService,
    ) {
    }

    public function reverse(
        Pembayaran $payment,
        string $reason,
        int $staffId,
    ): Pembayaran {
        return DB::transaction(function () use ($payment, $reason, $staffId): Pembayaran {
            $loan = Pinjaman::query()
                ->whereKey($payment->pinjaman_id)
                ->lockForUpdate()
                ->firstOrFail();
            $original = Pembayaran::query()
                ->whereKey($payment->id)
                ->where('pinjaman_id', $loan->id)
                ->lockForUpdate()
                ->firstOrFail();
            $hasReversal = Pembayaran::query()
                ->where('pembalikan_dari_id', $original->id)
                ->exists();

            if (
                $original->status !== 'tercatat'
                || $original->pembalikan_dari_id !== null
                || $hasReversal
            ) {
                throw ValidationException::withMessages([
                    'pembayaran' => 'Pembayaran ini sudah dibatalkan atau tidak dapat dibatalkan.',
                ]);
            }

            $original->update([
                'status' => 'dibatalkan',
                'alasan_koreksi' => $reason,
            ]);

            $reversal = Pembayaran::create([
                'nomor_pembayaran' => $this->nextPaymentNumber(),
                'pinjaman_id' => $original->pinjaman_id,
                'tanggal_pembayaran' => today()->toDateString(),
                'nominal' => $original->nominal,
                'metode' => $original->metode,
                'referensi' => $original->referensi,
                'status' => 'pembalikan',
                'dibuat_oleh' => $staffId,
                'pembalikan_dari_id' => $original->id,
                'alasan_koreksi' => $reason,
            ]);

            LogAktivitas::create([
                'user_id' => $staffId,
                'aksi' => 'loan.payment.reversed',
                'subjek_tipe' => Pembayaran::class,
                'subjek_id' => $original->id,
                'alasan' => $reason,
                'perubahan' => [
                    'status' => ['old' => 'tercatat', 'new' => 'dibatalkan'],
                    'pembalikan_id' => ['new' => $reversal->id],
                    'nomor_pembayaran' => ['new' => $reversal->nomor_pembayaran],
                ],
                'terjadi_pada' => now(),
            ]);

            $this->paymentService->refreshLoanState($loan->fresh());

            return $reversal;
        });
    }

    private function nextPaymentNumber(): string
    {
        do {
            $number = 'BYR-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Pembayaran::query()->where('nomor_pembayaran', $number)->exists());

        return $number;
    }
}
