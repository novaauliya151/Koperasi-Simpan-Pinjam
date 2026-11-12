<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const ALLOWED_FIELDS = [
        '_token',
        '_method',
        'telepon',
        'alamat',
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function edit(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user()->load('anggota'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $unexpectedFields = array_diff(
            array_keys($request->all()),
            self::ALLOWED_FIELDS,
        );

        if ($unexpectedFields !== []) {
            throw ValidationException::withMessages([
                'profile' => 'Profil hanya mengizinkan perubahan kontak dan kata sandi.',
            ]);
        }

        $data = $request->validate([
            'telepon' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if ($user->anggota !== null && array_key_exists('telepon', $data)) {
            $user->anggota->update([
                'telepon' => $data['telepon'],
                'alamat' => $data['alamat'] ?? $user->anggota->alamat,
            ]);
        }

        if ($user->anggota !== null && array_key_exists('alamat', $data)) {
            $user->anggota->update(['alamat' => $data['alamat']]);
        }

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}


