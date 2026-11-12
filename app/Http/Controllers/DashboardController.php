<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\LogAktivitas;
use App\Models\Pinjaman;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();

        $data = match ($user->role) {
            'admin' => $this->adminData(),
            'petugas' => $this->staffData(),
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

        return [
            'activeMembers' => Anggota::where('status_keanggotaan', 'aktif')->count(),
            'pendingMembers' => Anggota::where(
                'status_keanggotaan',
                'menunggu_verifikasi',
            )->count(),
            'savingsTotal' => TransaksiSimpanan::where('status', 'tercatat')->sum('nominal'),
            'activeLoans' => Pinjaman::where('status', 'aktif')->count(),
            'activeLoanTotal' => Pinjaman::where('status', 'aktif')->sum('pokok'),
            'monthlySavings' => $monthlySavings,
            'hasSavingsActivity' => $savings->isNotEmpty(),
            'recentActivity' => LogAktivitas::with('user:id,name')
                ->latest('terjadi_pada')
                ->limit(5)
                ->get(),
        ];
    }

    private function staffData(): array
    {
        return [
            'members' => Anggota::count(),
            'pendingMembers' => Anggota::where(
                'status_keanggotaan',
                'menunggu_verifikasi',
            )->count(),
            'activeMembers' => Anggota::where('status_keanggotaan', 'aktif')->count(),
            'recentMembers' => Anggota::latest('created_at')->limit(5)->get(),
        ];
    }

    private function memberData(User $user): array
    {
        $member = $user->anggota;

        return [
            'member' => $member,
            'savingsTotal' => $member
                ? TransaksiSimpanan::where('anggota_id', $member->id)
                    ->where('status', 'tercatat')
                    ->sum('nominal')
                : 0,
            'savingsCount' => $member?->simpanan()->where('status', 'tercatat')->count() ?? 0,
            'activeLoans' => $member
                ? Pinjaman::where('anggota_id', $member->id)
                    ->where('status', 'aktif')
                    ->count()
                : 0,
            'activeLoanTotal' => $member
                ? Pinjaman::where('anggota_id', $member->id)
                    ->where('status', 'aktif')
                    ->sum('pokok')
                : 0,
            'recentSavings' => $member
                ? $member->simpanan()
                    ->with('jenis')
                    ->where('status', 'tercatat')
                    ->latest('tanggal_transaksi')
                    ->limit(5)
                    ->get()
                : collect(),
        ];
    }
}
