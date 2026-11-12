<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    private const ROLES = ['admin', 'petugas', 'anggota'];

    public function index(Request $request): View
    {
        $accounts = User::query()
            ->with('anggota')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = $request->string('q')->trim()->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where(
                'role',
                $request->string('role')->toString(),
            ))
            ->when($request->filled('status'), fn ($query) => $query->where(
                'is_active',
                $request->string('status')->toString() === 'active',
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.accounts.index', [
            'accounts' => $accounts,
            'roles' => self::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('admin.accounts.create', [
            'roles' => self::ROLES,
            'members' => $this->availableMembers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(self::ROLES)],
            'anggota_id' => [
                'nullable',
                'required_if:role,anggota',
                'integer',
                Rule::exists('anggotas', 'id')->whereNull('user_id'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        $memberId = $data['role'] === 'anggota' ? ($data['anggota_id'] ?? null) : null;
        unset($data['anggota_id']);

        DB::transaction(function () use ($data, $memberId, $request): void {
            $user = User::create($data);

            if ($memberId !== null) {
                Anggota::query()
                    ->whereKey($memberId)
                    ->whereNull('user_id')
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->update(['user_id' => $user->id]);
            }

            LogAktivitas::create([
                'user_id' => $request->user()->id,
                'aksi' => 'account.created',
                'subjek_tipe' => User::class,
                'subjek_id' => $user->id,
                'perubahan' => [
                    'role' => ['new' => $user->role],
                    'is_active' => ['new' => $user->is_active],
                    'anggota_id' => ['new' => $memberId],
                ],
                'terjadi_pada' => now(),
            ]);
        });

        return redirect()->route('admin.accounts.index')
            ->with('status', 'Akun berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $members = Anggota::query()
            ->where(function ($query) use ($user): void {
                $query->where(function ($query): void {
                    $query->whereNull('user_id')
                        ->where('status_keanggotaan', 'aktif');
                })->orWhere('user_id', $user->id);
            })
            ->orderBy('nama')
            ->get();

        return view('admin.accounts.edit', [
            'account' => $user->load('anggota'),
            'roles' => self::ROLES,
            'members' => $members,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'anggota_id' => [
                'nullable',
                'required_if:role,anggota',
                'integer',
                Rule::exists('anggotas', 'id')->where(function ($query) use ($user): void {
                    $query->whereNull('user_id')->orWhere('user_id', $user->id);
                }),
            ],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->user()->is($user)
            && ($data['role'] !== $user->role || ! (bool) $data['is_active'])) {
            return back()->withErrors([
                'role' => 'Akun admin yang sedang digunakan tidak boleh dinonaktifkan atau diubah perannya.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        $memberId = $data['role'] === 'anggota' ? ($data['anggota_id'] ?? null) : null;
        unset($data['anggota_id']);

        if (blank($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($data, $memberId, $request, $user): void {
            $before = $user->only(['name', 'email', 'role', 'is_active']);
            $previousMember = $user->anggota;

            if ($previousMember !== null && ($data['role'] !== 'anggota' || $memberId != $previousMember->id)) {
                $previousMember->update(['user_id' => null]);
            }

            if ($data['role'] === 'anggota') {
                Anggota::query()
                    ->whereKey($memberId)
                    ->where(function ($query) use ($user): void {
                        $query->whereNull('user_id')->orWhere('user_id', $user->id);
                    })
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->update(['user_id' => $user->id]);
            }

            $user->update($data);

            $changes = [];
            foreach (['name', 'email', 'role', 'is_active'] as $field) {
                if ($before[$field] !== $user->getAttribute($field)) {
                    $changes[$field] = [
                        'old' => $before[$field],
                        'new' => $user->getAttribute($field),
                    ];
                }
            }

            if (! empty($data['password'])) {
                $changes['password'] = ['changed' => true];
            }

            if ($memberId !== null && $memberId != $previousMember?->id) {
                $changes['anggota_id'] = [
                    'old' => $previousMember?->id,
                    'new' => $memberId,
                ];
            }

            if ($changes !== []) {
                LogAktivitas::create([
                    'user_id' => $request->user()->id,
                    'aksi' => 'account.updated',
                    'subjek_tipe' => User::class,
                    'subjek_id' => $user->id,
                    'perubahan' => $changes,
                    'terjadi_pada' => now(),
                ]);
            }
        });

        return redirect()->route('admin.accounts.index')
            ->with('status', 'Akun berhasil diperbarui.');
    }

    private function availableMembers()
    {
        return Anggota::query()
            ->whereNull('user_id')
            ->where('status_keanggotaan', 'aktif')
            ->orderBy('nama')
            ->get();
    }
}


