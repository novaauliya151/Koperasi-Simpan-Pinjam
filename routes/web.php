<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Staff\MemberController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
        Route::resource('akun', AccountController::class)
            ->parameters(['akun' => 'user'])
            ->only(['index', 'create', 'store', 'edit', 'update'])
            ->names('accounts');
    });

    Route::middleware('role:petugas')->prefix('petugas')->name('petugas.')->group(function (): void {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
        Route::patch('/anggota/{anggota}/status', [MemberController::class, 'updateStatus'])
            ->name('anggota.status');
        Route::resource('anggota', MemberController::class)
            ->parameters(['anggota' => 'anggota'])
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });

    Route::middleware('role:anggota')->prefix('anggota')->name('anggota.')->group(function (): void {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
    });
});

Route::redirect('/', '/dashboard');
