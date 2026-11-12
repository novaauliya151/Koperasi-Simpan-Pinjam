<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'aksi' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $logs = LogAktivitas::query()
            ->with('user:id,name,email')
            ->when($filters['aksi'] ?? null, fn ($query, string $action) => $query->where(
                'aksi',
                'like',
                "%{$action}%",
            ))
            ->when($filters['user_id'] ?? null, fn ($query, int $userId) => $query->where(
                'user_id',
                $userId,
            ))
            ->when($filters['dari'] ?? null, fn ($query, string $from) => $query->whereDate(
                'terjadi_pada',
                '>=',
                $from,
            ))
            ->when($filters['sampai'] ?? null, fn ($query, string $to) => $query->whereDate(
                'terjadi_pada',
                '<=',
                $to,
            ))
            ->orderByDesc('terjadi_pada')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}

