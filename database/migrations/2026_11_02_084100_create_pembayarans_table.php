<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_pembayaran', 40)->unique();
            $table->foreignId('pinjaman_id')->constrained('pinjamans')->restrictOnDelete();
            $table->date('tanggal_pembayaran')->index();
            $table->decimal('nominal', 15, 2);
            $table->string('metode', 40)->nullable();
            $table->string('referensi', 100)->nullable();
            $table->string('status', 20)->default('tercatat')->index();
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('pembalikan_dari_id')->nullable()->unique()->constrained('pembayarans')->restrictOnDelete();
            $table->text('alasan_koreksi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
