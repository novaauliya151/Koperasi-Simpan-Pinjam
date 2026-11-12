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
        'pembalikan_dari_id', 'alasan_koreksi', 'jenis_simpanan_kode_snapshot',
        'jenis_simpanan_nama_snapshot', 'nominal_ketentuan_snapshot',
        'ketentuan_simpanan_id', 'idempotency_key', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_transaksi' => 'date',
            'nominal' => 'decimal:2',
            'nominal_ketentuan_snapshot' => 'decimal:2',
        ];
    }

    public function anggota(): BelongsTo { return $this->belongsTo(Anggota::class, 'anggota_id'); }
    public function jenis(): BelongsTo { return $this->belongsTo(JenisSimpanan::class, 'jenis_simpanan_id'); }
    public function petugas(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function ketentuanSnapshot(): BelongsTo
    {
        return $this->belongsTo(KetentuanSimpanan::class, 'ketentuan_simpanan_id');
    }
}



