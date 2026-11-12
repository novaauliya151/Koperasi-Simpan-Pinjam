<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjamans', function (Blueprint $table): void {
            $table->id();
            $table->string('nomor_pinjaman', 40)->unique();
            $table->foreignId('anggota_id')->constrained('anggotas')->restrictOnDelete();
            $table->foreignId('ketentuan_pinjaman_id')->nullable()->constrained('ketentuan_pinjaman')->nullOnDelete();
            $table->decimal('pokok', 15, 2);
            $table->unsignedSmallInteger('tenor_bulan');
            $table->date('tanggal_pencairan');
            $table->date('angsuran_pertama_jatuh_tempo');
            $table->string('referensi_persetujuan', 100);
            $table->decimal('jasa_flat_bulanan_snapshot', 8, 5)->default(0);
            $table->json('snapshot_ketentuan');
            $table->string('status', 20)->default('aktif')->index();
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['anggota_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjamans');
    }
};
