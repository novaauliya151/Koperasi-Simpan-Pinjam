<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiSimpanan extends Model
{
    protected $table = 'transaksi_simpanans';

    protected $fillable = [
        'nomor_transaksi', 'anggota_id', 'jenis_simpanan_id', 'tanggal_transaksi',
        'periode', 'nominal', 'metode', 'status', 'dibuat_oleh',
        'pembalikan_dari_id', 'alasan_koreksi',
    ];

    protected function casts(): array
    {
        return ['tanggal_transaksi' => 'date', 'nominal' => 'decimal:2'];
    }

    public function anggota(): BelongsTo { return $this->belongsTo(Anggota::class, 'anggota_id'); }
    public function jenis(): BelongsTo { return $this->belongsTo(JenisSimpanan::class, 'jenis_simpanan_id'); }
    public function petugas(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh'); }
}
