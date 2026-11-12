<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->string('referensi_pencairan', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pinjamans', function (Blueprint $table): void {
            $table->dropColumn('referensi_pencairan');
        });
    }
};
