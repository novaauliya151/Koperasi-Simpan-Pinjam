<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;

class LoanScheduleService
{
    public function ensure(Pinjaman $loan): void
    {
        DB::transaction(function () use ($loan): void {
            $lockedLoan = Pinjaman::query()
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureWithinTransaction($lockedLoan);
        });
    }

    public function ensureWithinTransaction(Pinjaman $loan): void
    {
        $existingCount = $loan->angsuran()->count();

        if ($existingCount === $loan->tenor_bulan) {
            return;
        }

        if ($existingCount > 0) {
            throw new LogicException('Jadwal pinjaman hanya tersimpan sebagian.');
        }

        $terms = $loan->snapshot_ketentuan ?? [];
        $method = $terms['metode'] ?? 'flat';

        if ($method !== 'flat') {
            throw new LogicException('Metode jadwal pinjaman ini belum didukung.');
        }

        $tenor = $loan->tenor_bulan;
        $principalCents = $this->toCents($loan->pokok);
        $baseInstallmentCents = $this->basePrincipalCents($principalCents, $tenor);
        $monthlyRate = (float) ($terms['jasa_flat_bulanan']
            ?? $loan->jasa_flat_bulanan_snapshot);
        $monthlyInterestRupiah = (int) round(
            ($principalCents / 100) * $monthlyRate / 100,
            0,
            PHP_ROUND_HALF_UP,
        );
        $monthlyInterestCents = $monthlyInterestRupiah * 100;
        $firstDueDate = Carbon::parse($loan->angsuran_pertama_jatuh_tempo);
        $allocatedPrincipalCents = 0;
        $now = now();
        $schedule = [];

        for ($number = 1; $number <= $tenor; $number++) {
            $principalInstallmentCents = $number === $tenor
                ? $principalCents - $allocatedPrincipalCents
                : $baseInstallmentCents;
            $allocatedPrincipalCents += $principalInstallmentCents;

            $schedule[] = [
                'pinjaman_id' => $loan->id,
                'urutan' => $number,
                'jatuh_tempo' => $firstDueDate
                    ->copy()
                    ->addMonthsNoOverflow($number - 1)
                    ->toDateString(),
                'pokok_tagihan' => $this->fromCents($principalInstallmentCents),
                'jasa_tagihan' => $this->fromCents($monthlyInterestCents),
                'status' => 'belum_dibayar',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Angsuran::query()->insert($schedule);
    }

    private function basePrincipalCents(int $principalCents, int $tenor): int
    {
        if ($tenor <= 1) {
            return 0;
        }

        $roundedRupiah = (int) round(
            $principalCents / 100 / $tenor,
            0,
            PHP_ROUND_HALF_UP,
        );
        $roundedCents = $roundedRupiah * 100;
        $maximumSafeCents = intdiv(
            intdiv($principalCents, $tenor - 1),
            100,
        ) * 100;

        return min($roundedCents, $maximumSafeCents);
    }

    private function toCents(string|int|float $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        $whole = intdiv($amount, 100);
        $fraction = str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);

        return $whole.'.'.$fraction;
    }
}
