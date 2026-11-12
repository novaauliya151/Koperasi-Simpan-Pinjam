<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angsurans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pinjaman_id')->constrained('pinjamans')->restrictOnDelete();
            $table->unsignedSmallInteger('urutan');
            $table->date('jatuh_tempo')->index();
            $table->decimal('pokok_tagihan', 15, 2);
            $table->decimal('jasa_tagihan', 15, 2)->default(0);
            $table->string('status', 20)->default('belum_dibayar')->index();
            $table->timestamps();
            $table->unique(['pinjaman_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angsurans');
    }
};
