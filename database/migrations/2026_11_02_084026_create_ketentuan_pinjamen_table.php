<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ketentuan_pinjaman', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->decimal('jasa_flat_bulanan', 8, 5)->default(0);
            $table->date('berlaku_mulai')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('ketentuan_pinjaman'); }
};
