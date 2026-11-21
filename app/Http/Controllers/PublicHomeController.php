<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Menyiapkan informasi yang diperlukan oleh halaman beranda publik. */
class PublicHomeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $configuredTerms = config('pinjaman.aturan');

        return view('public.home', [
            'loanTerms' => [
                'method' => $configuredTerms['metode'],
                'monthly_rate' => (float) $configuredTerms['jasa_flat_bulanan'],
                'tenors' => $configuredTerms['tenor_bulan'],
                'is_assumption' => true,
            ],
        ]);
    }
}




