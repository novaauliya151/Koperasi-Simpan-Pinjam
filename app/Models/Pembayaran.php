<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pembayaran extends Model
{
    protected $table = 'pembayarans';

    protected $fillable = [
        'nomor_pembayaran',
        'pinjaman_id',
        'tanggal_pembayaran',
        'nominal',
        'metode',
        'referensi',
        'status',
        'dibuat_oleh',
        'pembalikan_dari_id',
        'alasan_koreksi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pembayaran' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id');
    }

    public function alokasi(): HasMany
    {
        return $this->hasMany(AlokasiPembayaran::class, 'pembayaran_id');
    }

    public function pembalikan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'pembalikan_dari_id');
    }

    public function pembatalan(): HasOne
    {
        return $this->hasOne(self::class, 'pembalikan_dari_id');
    }
}
