<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    protected $table = 'pinjamans';

    protected $fillable = [
        'nomor_pinjaman',
        'anggota_id',
        'ketentuan_pinjaman_id',
        'pokok',
        'tenor_bulan',
        'tanggal_pencairan',
        'angsuran_pertama_jatuh_tempo',
        'referensi_persetujuan',
        'referensi_pencairan',
        'jasa_flat_bulanan_snapshot',
        'snapshot_ketentuan',
        'status',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'pokok' => 'decimal:2',
            'tenor_bulan' => 'integer',
            'tanggal_pencairan' => 'date',
            'angsuran_pertama_jatuh_tempo' => 'date',
            'jasa_flat_bulanan_snapshot' => 'decimal:5',
            'snapshot_ketentuan' => 'array',
        ];
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function ketentuan(): BelongsTo
    {
        return $this->belongsTo(KetentuanPinjaman::class, 'ketentuan_pinjaman_id');
    }

    public function angsuran(): HasMany
    {
        return $this->hasMany(Angsuran::class, 'pinjaman_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'pinjaman_id');
    }
}
