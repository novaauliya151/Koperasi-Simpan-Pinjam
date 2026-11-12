<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JenisSimpanan extends Model
{
    protected $table = 'jenis_simpanans';

    protected $fillable = [
        'kode',
        'nama',
        'nominal_ketentuan',
        'berlaku_mulai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'nominal_ketentuan' => 'decimal:2',
            'berlaku_mulai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiSimpanan::class, 'jenis_simpanan_id');
    }

    public function ketentuan(): HasMany
    {
        return $this->hasMany(KetentuanSimpanan::class, 'jenis_simpanan_id');
    }

    public function ketentuanTerbaru(): HasOne
    {
        return $this->hasOne(KetentuanSimpanan::class, 'jenis_simpanan_id')
            ->ofMany('berlaku_mulai', 'max');
    }
}
