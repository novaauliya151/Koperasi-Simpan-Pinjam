<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\LoanTermsController;
use App\Http\Controllers\Admin\SavingTypeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Staff\LoanController;
use App\Http\Controllers\Staff\InstallmentPaymentController;
use App\Http\Controllers\Staff\MemberController;
use App\Http\Controllers\Staff\SavingsTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('akun', AccountController::class)
            ->parameters(['akun' => 'user'])
            ->only(['index', 'create', 'store', 'edit', 'update'])
            ->names('accounts');

        Route::get('/jenis-simpanan', [SavingTypeController::class, 'index'])
            ->name('saving-types.index');
        Route::get('/jenis-simpanan/create', [SavingTypeController::class, 'create'])
            ->name('saving-types.create');
        Route::post('/jenis-simpanan', [SavingTypeController::class, 'store'])
            ->name('saving-types.store');
        Route::get('/jenis-simpanan/{savingType}/edit', [SavingTypeController::class, 'edit'])
            ->name('saving-types.edit');
        Route::put('/jenis-simpanan/{savingType}', [SavingTypeController::class, 'update'])
            ->name('saving-types.update');
        Route::patch('/jenis-simpanan/{savingType}/status', [SavingTypeController::class, 'updateStatus'])
            ->name('saving-types.status');
        Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');
        Route::get('/ketentuan-pinjaman', [LoanTermsController::class, 'index'])
            ->name('loan-terms.index');
        Route::post('/ketentuan-pinjaman', [LoanTermsController::class, 'store'])
            ->name('loan-terms.store');
    });

    Route::middleware('role:petugas')->prefix('petugas')->name('petugas.')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/pinjaman', [LoanController::class, 'staffIndex'])
            ->name('pinjaman.index');
        Route::get('/pinjaman/{loan}', [LoanController::class, 'staffShow'])
            ->name('pinjaman.show');
        Route::get('/pinjaman/create', [LoanController::class, 'create'])
            ->name('pinjaman.create');
        Route::get('/pinjaman/{loan}/pembayaran/create', [InstallmentPaymentController::class, 'create'])
            ->name('pinjaman.pembayaran.create');
        Route::post('/pinjaman/{loan}/pembayaran', [InstallmentPaymentController::class, 'store'])
            ->name('pinjaman.pembayaran.store');
        Route::post('/pinjaman', [LoanController::class, 'store'])
            ->name('pinjaman.store');
        Route::get('/simpanan', [SavingsTransactionController::class, 'staffIndex'])
            ->name('simpanan.index');
        Route::get('/simpanan/create', [SavingsTransactionController::class, 'create'])
            ->name('simpanan.create');
        Route::post('/simpanan', [SavingsTransactionController::class, 'store'])
            ->name('simpanan.store');
        Route::get('/simpanan/{transaction}/bukti', [SavingsTransactionController::class, 'receipt'])
            ->name('simpanan.receipt');
        Route::post('/simpanan/{transaction}/pembatalan', [SavingsTransactionController::class, 'cancel'])
            ->name('simpanan.cancel');
        Route::patch('/anggota/{anggota}/status', [MemberController::class, 'updateStatus'])
            ->name('anggota.status');
        Route::resource('anggota', MemberController::class)
            ->parameters(['anggota' => 'anggota'])
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });

    Route::middleware('role:anggota')->prefix('anggota')->name('anggota.')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/pinjaman', [LoanController::class, 'memberIndex'])
            ->name('pinjaman.index');
        Route::get('/pinjaman/{loan}', [LoanController::class, 'memberShow'])
            ->name('pinjaman.show');
        Route::get('/simpanan', [SavingsTransactionController::class, 'memberIndex'])
            ->name('simpanan.index');
        Route::get('/simpanan/{transaction}/bukti', [SavingsTransactionController::class, 'receipt'])
            ->name('simpanan.receipt');
    });
});

Route::redirect('/', '/dashboard');

