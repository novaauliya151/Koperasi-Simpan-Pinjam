<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KetentuanPinjaman;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoanTermsController extends Controller
{
    public function index(): View
    {
        $currentRule = KetentuanPinjaman::query()
            ->whereDate('berlaku_mulai', '<=', today())
            ->orderByDesc('berlaku_mulai')
            ->orderByDesc('id')
            ->first();
        $latestRule = KetentuanPinjaman::query()
            ->orderByDesc('berlaku_mulai')
            ->orderByDesc('id')
            ->first();
        $suggestedDate = $latestRule?->berlaku_mulai?->copy()->addDay()->toDateString();
        $effectiveDateDefault = $suggestedDate && $suggestedDate > today()->toDateString()
            ? $suggestedDate
            : today()->toDateString();

        return view('admin.loan-terms.index', [
            'currentRule' => $currentRule,
            'terms' => $currentRule ? $this->toTerms($currentRule) : config('pinjaman.aturan'),
            'effectiveDateDefault' => $effectiveDateDefault,
            'history' => KetentuanPinjaman::query()
                ->withCount('pinjaman')
                ->orderByDesc('berlaku_mulai')
                ->orderByDesc('id')
                ->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jasa_flat_bulanan' => [
                'required',
                'numeric',
                'decimal:0,5',
                'gt:0',
                'max:999.99999',
            ],
            'tenor_bulan' => [
                'required',
                'string',
                'max:100',
                'regex:/^\d+(,\s*\d+)*$/',
            ],
            'berlaku_mulai' => ['required', 'date', 'after_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenors = array_map(
            'intval',
            preg_split('/\s*,\s*/', $data['tenor_bulan']),
        );

        if (
            count($tenors) !== count(array_unique($tenors))
            || min($tenors) < 1
            || max($tenors) > 65535
        ) {
            throw ValidationException::withMessages([
                'tenor_bulan' => 'Masukkan tenor bulan positif tanpa duplikasi.',
            ]);
        }

        $dateExists = KetentuanPinjaman::query()
            ->whereDate('berlaku_mulai', $data['berlaku_mulai'])
            ->exists();

        if ($dateExists) {
            throw ValidationException::withMessages([
                'berlaku_mulai' => 'Sudah ada versi ketentuan pada tanggal tersebut.',
            ]);
        }

        $latestRule = KetentuanPinjaman::query()
            ->orderByDesc('berlaku_mulai')
            ->orderByDesc('id')
            ->first();

        if (
            $latestRule?->berlaku_mulai
            && $data['berlaku_mulai'] <= $latestRule->berlaku_mulai->toDateString()
        ) {
            throw ValidationException::withMessages([
                'berlaku_mulai' => 'Versi baru harus berlaku setelah versi ketentuan terakhir.',
            ]);
        }

        DB::transaction(function () use ($data, $request, $tenors): void {
            $rule = KetentuanPinjaman::create([
                'nama' => $data['nama'],
                'jasa_flat_bulanan' => $data['jasa_flat_bulanan'],
                'berlaku_mulai' => $data['berlaku_mulai'],
                'is_active' => true,
                'metode_perhitungan' => 'flat',
                'tenor_bulan' => $tenors,
                'pembulatan' => config('pinjaman.aturan.pembulatan'),
                'dasar' => 'Asumsi simulasi proyek koperasi konvensional.',
                'sumber' => config('pinjaman.aturan.sumber'),
                'keterangan' => $data['catatan'] ?? null,
            ]);

            LogAktivitas::create([
                'user_id' => $request->user()->id,
                'aksi' => 'loan_terms.created',
                'subjek_tipe' => KetentuanPinjaman::class,
                'subjek_id' => $rule->id,
                'perubahan' => [
                    'nama' => ['new' => $rule->nama],
                    'jasa_flat_bulanan' => ['new' => $rule->jasa_flat_bulanan],
                    'tenor_bulan' => ['new' => $tenors],
                    'berlaku_mulai' => ['new' => $rule->berlaku_mulai->toDateString()],
                    'catatan' => ['new' => $data['catatan'] ?? null],
                ],
                'terjadi_pada' => now(),
            ]);
        });

        return redirect()->route('admin.loan-terms.index')
            ->with('status', 'Versi ketentuan pinjaman berhasil disimpan.');
    }

    private function toTerms(KetentuanPinjaman $rule): array
    {
        return [
            'kode' => 'KETENTUAN-'.$rule->id,
            'nama' => $rule->nama,
            'metode' => $rule->metode_perhitungan,
            'jasa_flat_bulanan' => $rule->jasa_flat_bulanan,
            'tenor_bulan' => $rule->tenor_bulan ?: config('pinjaman.aturan.tenor_bulan'),
            'pembulatan' => $rule->pembulatan,
            'dasar' => $rule->dasar,
            'catatan' => $rule->keterangan,
            'sumber' => $rule->sumber,
            'referensi' => config('pinjaman.aturan.referensi'),
        ];
    }
}
