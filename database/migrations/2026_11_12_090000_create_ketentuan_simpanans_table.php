<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ketentuan_simpanans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('jenis_simpanan_id')
                ->constrained('jenis_simpanans')
                ->restrictOnDelete();
            $table->decimal('nominal_ketentuan', 15, 2)->nullable();
            $table->date('berlaku_mulai');
            $table->foreignId('dibuat_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['jenis_simpanan_id', 'berlaku_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ketentuan_simpanans');
    }
};
