<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_simpanans', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->text('keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_simpanans', function (Blueprint $table): void {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['idempotency_key', 'keterangan']);
        });
    }
};
