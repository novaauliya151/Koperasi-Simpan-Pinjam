<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\LogAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    private const STATUSES = [
        'menunggu_verifikasi',
        'aktif',
        'tidak_aktif',
        'keluar',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
        ]);

        $members = Anggota::query()
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nomor_anggota', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where(
                'status_keanggotaan',
                $status,
            ))
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('staff.members.index', [
            'members' => $members,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('staff.members.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nomor_anggota' => ['required', 'string', 'max:30', 'unique:anggotas,nomor_anggota'],
            'nama' => ['required', 'string', 'max:255'],
            'nomor_identitas' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'tanggal_bergabung' => ['nullable', 'date'],
        ]);

        $member = DB::transaction(function () use ($data, $request): Anggota {
            $member = Anggota::create([
                ...$data,
                'status_keanggotaan' => 'menunggu_verifikasi',
            ]);

            $this->logChange($request, $member, 'member.created', ['status_keanggotaan']);

            return $member;
        });

        return redirect()->route('petugas.anggota.edit', $member)
            ->with('status', 'Data anggota berhasil dibuat dan menunggu verifikasi.');
    }

    public function edit(Anggota $anggota): View
    {
        return view('staff.members.edit', [
            'member' => $anggota->load('user'),
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, Anggota $anggota): RedirectResponse
    {
        $data = $request->validate([
            'nomor_anggota' => [
                'required',
                'string',
                'max:30',
                Rule::unique('anggotas', 'nomor_anggota')->ignore($anggota->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'nomor_identitas' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'tanggal_bergabung' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($data, $request, $anggota): void {
            $changedFields = array_keys(array_diff_assoc($data, $anggota->only(array_keys($data))));
            $anggota->update($data);
            $this->logChange($request, $anggota, 'member.updated', $changedFields);
        });

        return redirect()->route('petugas.anggota.edit', $anggota)
            ->with('status', 'Data anggota berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Anggota $anggota): RedirectResponse
    {
        $data = $request->validate([
            'status_keanggotaan' => [
                'required',
                Rule::in(self::STATUSES),
            ],
        ]);

        $oldStatus = $anggota->status_keanggotaan;
        $newStatus = $data['status_keanggotaan'];

        DB::transaction(function () use ($request, $anggota, $oldStatus, $newStatus): void {
            $anggota->update([
                'status_keanggotaan' => $newStatus,
                'diverifikasi_oleh' => $newStatus === 'menunggu_verifikasi'
                    ? null
                    : $request->user()->id,
                'diverifikasi_pada' => $newStatus === 'menunggu_verifikasi'
                    ? null
                    : now(),
            ]);

            LogAktivitas::create([
                'user_id' => $request->user()->id,
                'aksi' => 'member.status_changed',
                'subjek_tipe' => Anggota::class,
                'subjek_id' => $anggota->id,
                'perubahan' => [
                    'status_keanggotaan' => [
                        'old' => $oldStatus,
                        'new' => $newStatus,
                    ],
                ],
                'terjadi_pada' => now(),
            ]);
        });

        return redirect()->route('petugas.anggota.index')
            ->with('status', 'Status keanggotaan berhasil diperbarui.');
    }

    private function logChange(
        Request $request,
        Anggota $member,
        string $action,
        array $changedFields,
    ): void {
        LogAktivitas::create([
            'user_id' => $request->user()->id,
            'aksi' => $action,
            'subjek_tipe' => Anggota::class,
            'subjek_id' => $member->id,
            'perubahan' => ['fields_changed' => $changedFields],
            'terjadi_pada' => now(),
        ]);
    }
}
