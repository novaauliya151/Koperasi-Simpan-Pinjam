<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ketentuan_pinjaman', function (Blueprint $table): void {
            $table->string('metode_perhitungan', 30)->default('flat');
            $table->json('tenor_bulan')->nullable();
            $table->text('pembulatan')->nullable();
            $table->text('dasar')->nullable();
            $table->text('sumber')->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ketentuan_pinjaman', function (Blueprint $table): void {
            $table->dropColumn([
                'metode_perhitungan',
                'tenor_bulan',
                'pembulatan',
                'dasar',
                'sumber',
                'keterangan',
            ]);
        });
    }
};
