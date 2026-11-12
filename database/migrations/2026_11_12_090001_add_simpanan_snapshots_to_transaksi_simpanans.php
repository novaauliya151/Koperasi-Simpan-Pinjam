<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_simpanans', function (Blueprint $table): void {
            $table->foreignId('ketentuan_simpanan_id')
                ->nullable()
                ->constrained('ketentuan_simpanans')
                ->restrictOnDelete();
            $table->string('jenis_simpanan_kode_snapshot', 30)->nullable();
            $table->string('jenis_simpanan_nama_snapshot')->nullable();
            $table->decimal('nominal_ketentuan_snapshot', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_simpanans', function (Blueprint $table): void {
            $table->dropForeign(['ketentuan_simpanan_id']);
            $table->dropColumn([
                'ketentuan_simpanan_id',
                'jenis_simpanan_kode_snapshot',
                'jenis_simpanan_nama_snapshot',
                'nominal_ketentuan_snapshot',
            ]);
        });
    }
};

