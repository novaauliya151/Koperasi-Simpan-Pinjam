<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
    });

    Route::middleware('role:petugas')->prefix('petugas')->name('petugas.')->group(function (): void {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
    });

    Route::middleware('role:anggota')->prefix('anggota')->name('anggota.')->group(function (): void {
        Route::view('/dashboard', 'dashboard')->name('dashboard');
    });
});

Route::redirect('/', '/dashboard');
