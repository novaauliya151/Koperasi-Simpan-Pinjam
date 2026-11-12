<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 100);
            $table->string('subjek_tipe', 100)->nullable();
            $table->unsignedBigInteger('subjek_id')->nullable();
            $table->text('alasan')->nullable();
            $table->json('perubahan')->nullable();
            $table->timestamp('terjadi_pada')->useCurrent()->index();
            $table->timestamps();
            $table->index(['subjek_tipe', 'subjek_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
