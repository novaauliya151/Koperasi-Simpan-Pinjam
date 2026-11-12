<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_simpanans', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_transaksi', 40)->unique();
            $table->foreignId('anggota_id')->constrained('anggotas')->restrictOnDelete();
            $table->foreignId('jenis_simpanan_id')->constrained('jenis_simpanans')->restrictOnDelete();
            $table->date('tanggal_transaksi')->index();
            $table->string('periode', 7)->nullable();
            $table->decimal('nominal', 15, 2);
            $table->string('metode', 40)->nullable();
            $table->string('status', 20)->default('tercatat')->index();
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('pembalikan_dari_id')->nullable()->unique()->constrained('transaksi_simpanans')->restrictOnDelete();
            $table->text('alasan_koreksi')->nullable();
            $table->timestamps();
            $table->index(['anggota_id', 'tanggal_transaksi']);
        });
    }

    public function down(): void { Schema::dropIfExists('transaksi_simpanans'); }
};
