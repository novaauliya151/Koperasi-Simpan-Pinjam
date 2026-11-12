<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSimpanan;
use App\Models\KetentuanSimpanan;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SavingTypeController extends Controller
{
    public function index(): View
    {
        $types = JenisSimpanan::query()
            ->with('ketentuanTerbaru')
            ->withCount('transaksi')
            ->orderBy('nama')
            ->paginate(15);

        return view('admin.savings.index', ['types' => $types]);
    }

    public function create(): View
    {
        return view('admin.savings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9_-]+$/', 'unique:jenis_simpanans,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'nominal_ketentuan' => ['nullable', 'numeric', 'min:0'],
            'berlaku_mulai' => ['required', 'date', 'after_or_equal:today'],
        ]);

        DB::transaction(function () use ($data, $request): void {
            $type = JenisSimpanan::create([
                ...$data,
                'is_active' => true,
            ]);

            $this->recordRule($type, $data, $request);
            $this->log($request, $type, 'saving_type.created', [
                'fields_changed' => ['kode', 'nama', 'nominal_ketentuan', 'berlaku_mulai', 'is_active'],
            ]);
        });

        return redirect()->route('admin.saving-types.index')
            ->with('status', 'Jenis simpanan berhasil dibuat.');
    }

    public function edit(JenisSimpanan $savingType): View
    {
        return view('admin.savings.edit', [
            'type' => $savingType->load('ketentuanTerbaru'),
            'history' => $savingType->ketentuan()
                ->orderByDesc('berlaku_mulai')
                ->paginate(10),
        ]);
    }

    public function update(Request $request, JenisSimpanan $savingType): RedirectResponse
    {
        $data = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('jenis_simpanans', 'kode')->ignore($savingType->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'nominal_ketentuan' => ['nullable', 'numeric', 'min:0'],
            'berlaku_mulai' => ['required', 'date', 'after_or_equal:today'],
            'is_active' => ['required', 'boolean'],
        ]);

        $latestRule = $savingType->ketentuan()
            ->orderByDesc('berlaku_mulai')
            ->first();

        if ($latestRule !== null
            && $data['berlaku_mulai'] < $latestRule->berlaku_mulai->toDateString()) {
            throw ValidationException::withMessages([
                'berlaku_mulai' => 'Tanggal berlaku harus sama dengan atau setelah versi terbaru.',
            ]);
        }

        $sameDateRule = $savingType->ketentuan()
            ->whereDate('berlaku_mulai', $data['berlaku_mulai'])
            ->first();

        if ($sameDateRule !== null
            && $sameDateRule->nominal_ketentuan !== $this->decimalValue($data['nominal_ketentuan'] ?? null)) {
            throw ValidationException::withMessages([
                'berlaku_mulai' => 'Aturan pada tanggal tersebut sudah tersimpan. Pilih tanggal berlaku baru.',
            ]);
        }

        DB::transaction(function () use ($data, $request, $savingType, $sameDateRule): void {
            $before = $savingType->only(['kode', 'nama', 'is_active']);

            if ($sameDateRule === null) {
                $this->recordRule($savingType, $data, $request);
            }

            $savingType->update([
                'kode' => $data['kode'],
                'nama' => $data['nama'],
                'nominal_ketentuan' => $data['nominal_ketentuan'] ?? null,
                'berlaku_mulai' => $data['berlaku_mulai'],
                'is_active' => $data['is_active'],
            ]);

            $changes = [];
            foreach (['kode', 'nama', 'is_active'] as $field) {
                if ($before[$field] !== $savingType->getAttribute($field)) {
                    $changes[$field] = [
                        'old' => $before[$field],
                        'new' => $savingType->getAttribute($field),
                    ];
                }
            }

            if ($sameDateRule === null) {
                $changes['aturan_baru'] = [
                    'nominal_ketentuan' => $data['nominal_ketentuan'] ?? null,
                    'berlaku_mulai' => $data['berlaku_mulai'],
                ];
            }

            if ($changes !== []) {
                $this->log($request, $savingType, 'saving_type.updated', $changes);
            }
        });

        return redirect()->route('admin.saving-types.index')
            ->with('status', 'Jenis simpanan berhasil diperbarui.');
    }

    public function updateStatus(Request $request, JenisSimpanan $savingType): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $savingType, $data): void {
            $oldStatus = $savingType->is_active;
            $savingType->update(['is_active' => $data['is_active']]);

            $this->log($request, $savingType, 'saving_type.status_changed', [
                'is_active' => [
                    'old' => $oldStatus,
                    'new' => $savingType->is_active,
                ],
            ]);
        });

        return redirect()->route('admin.saving-types.index')
            ->with('status', 'Status jenis simpanan berhasil diperbarui.');
    }

    private function recordRule(
        JenisSimpanan $type,
        array $data,
        Request $request,
    ): KetentuanSimpanan {
        return $type->ketentuan()->create([
            'nominal_ketentuan' => $data['nominal_ketentuan'] ?? null,
            'berlaku_mulai' => $data['berlaku_mulai'],
            'dibuat_oleh' => $request->user()->id,
        ]);
    }

    private function log(
        Request $request,
        JenisSimpanan $type,
        string $action,
        array $changes,
    ): void {
        LogAktivitas::create([
            'user_id' => $request->user()->id,
            'aksi' => $action,
            'subjek_tipe' => JenisSimpanan::class,
            'subjek_id' => $type->id,
            'perubahan' => $changes,
            'terjadi_pada' => now(),
        ]);
    }

    private function decimalValue(mixed $value): ?string
    {
        return $value === null ? null : number_format((float) $value, 2, '.', '');
    }
}


