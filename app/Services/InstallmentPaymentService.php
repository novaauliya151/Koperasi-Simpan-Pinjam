<?php

namespace App\Services;

use App\Models\AlokasiPembayaran;
use App\Models\Angsuran;
use App\Models\LogAktivitas;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InstallmentPaymentService
{
    public function recordFullInstallment(
        Pinjaman $loan,
        int $installmentId,
        string $paymentDate,
        string $method,
        ?string $reference,
        int $staffId,
    ): Pembayaran {
        return DB::transaction(function () use (
            $loan,
            $installmentId,
            $paymentDate,
            $method,
            $reference,
            $staffId,
        ): Pembayaran {
            $lockedLoan = Pinjaman::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();
            $installment = Angsuran::query()
                ->where('pinjaman_id', $lockedLoan->id)
                ->whereKey($installmentId)
                ->lockForUpdate()
                ->first();

            if ($installment === null) {
                throw ValidationException::withMessages([
                    'angsuran_id' => 'Angsuran tidak ditemukan pada pinjaman ini.',
                ]);
            }

            $installment->load('alokasi.pembayaran:id,status');
            $validAllocations = $installment->alokasi->filter(
                fn (AlokasiPembayaran $allocation): bool =>
                    $allocation->pembayaran?->status === 'tercatat',
            );
            $remainingPrincipalCents = max(
                0,
                $this->toCents($installment->pokok_tagihan)
                    - $validAllocations->sum(fn (AlokasiPembayaran $allocation): int =>
                        $this->toCents($allocation->alokasi_pokok)),
            );
            $remainingInterestCents = max(
                0,
                $this->toCents($installment->jasa_tagihan)
                    - $validAllocations->sum(fn (AlokasiPembayaran $allocation): int =>
                        $this->toCents($allocation->alokasi_jasa)),
            );
            $amountCents = $remainingPrincipalCents + $remainingInterestCents;

            if ($amountCents <= 0) {
                throw ValidationException::withMessages([
                    'angsuran_id' => 'Angsuran ini sudah lunas.',
                ]);
            }

            if ($lockedLoan->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'angsuran_id' => 'Pinjaman ini sudah lunas dan tidak menerima pembayaran baru.',
                ]);
            }

            if ($installment->jatuh_tempo->gt($paymentDate)) {
                throw ValidationException::withMessages([
                    'angsuran_id' => 'Angsuran belum jatuh tempo pada tanggal pembayaran tersebut.',
                ]);
            }

            $payment = Pembayaran::create([
                'nomor_pembayaran' => $this->nextPaymentNumber(),
                'pinjaman_id' => $lockedLoan->id,
                'tanggal_pembayaran' => $paymentDate,
                'nominal' => $this->fromCents($amountCents),
                'metode' => $method,
                'referensi' => $reference,
                'status' => 'tercatat',
                'dibuat_oleh' => $staffId,
            ]);

            AlokasiPembayaran::create([
                'pembayaran_id' => $payment->id,
                'angsuran_id' => $installment->id,
                'alokasi_pokok' => $this->fromCents($remainingPrincipalCents),
                'alokasi_jasa' => $this->fromCents($remainingInterestCents),
            ]);

            LogAktivitas::create([
                'user_id' => $staffId,
                'aksi' => 'loan.payment.recorded',
                'subjek_tipe' => Pembayaran::class,
                'subjek_id' => $payment->id,
                'perubahan' => [
                    'nomor_pembayaran' => ['new' => $payment->nomor_pembayaran],
                    'pinjaman_id' => ['new' => $lockedLoan->id],
                    'angsuran_id' => ['new' => $installment->id],
                    'nominal' => ['new' => $payment->nominal],
                ],
                'terjadi_pada' => now(),
            ]);

            $this->refreshLoanState($lockedLoan->fresh());

            return $payment;
        });
    }

    public function refreshLoanState(Pinjaman $loan): void
    {
        $loan->load('angsuran.alokasi.pembayaran:id,status');

        if ($loan->angsuran->isEmpty()) {
            return;
        }

        $allPaid = true;

        foreach ($loan->angsuran as $installment) {
            $allocations = $installment->alokasi->filter(
                fn (AlokasiPembayaran $allocation): bool =>
                    $allocation->pembayaran?->status === 'tercatat',
            );
            $paidPrincipalCents = $allocations->sum(
                fn (AlokasiPembayaran $allocation): int =>
                    $this->toCents($allocation->alokasi_pokok),
            );
            $paidInterestCents = $allocations->sum(
                fn (AlokasiPembayaran $allocation): int =>
                    $this->toCents($allocation->alokasi_jasa),
            );
            $outstandingCents = max(
                0,
                $this->toCents($installment->pokok_tagihan)
                    + $this->toCents($installment->jasa_tagihan)
                    - $paidPrincipalCents
                    - $paidInterestCents,
            );

            if ($outstandingCents === 0) {
                $status = 'dibayar';
            } else {
                $allPaid = false;
                $status = $installment->jatuh_tempo->lt(today())
                    ? 'terlambat'
                    : 'belum_dibayar';
            }

            if ($installment->status !== $status) {
                $installment->update(['status' => $status]);
            }
        }

        $loanStatus = $allPaid ? 'lunas' : 'aktif';

        if ($loan->status !== $loanStatus) {
            $loan->update(['status' => $loanStatus]);
        }
    }

    private function nextPaymentNumber(): string
    {
        do {
            $number = 'BYR-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Pembayaran::query()->where('nomor_pembayaran', $number)->exists());

        return $number;
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
