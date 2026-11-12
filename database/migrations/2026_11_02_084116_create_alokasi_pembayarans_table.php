<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alokasi_pembayarans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->restrictOnDelete();
            $table->foreignId('angsuran_id')->constrained('angsurans')->restrictOnDelete();
            $table->decimal('alokasi_pokok', 15, 2)->default(0);
            $table->decimal('alokasi_jasa', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['pembayaran_id', 'angsuran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_pembayarans');
    }
};
