<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KetentuanPinjaman extends Model
{
    protected $table = 'ketentuan_pinjaman';

    protected $fillable = [
        'nama',
        'jasa_flat_bulanan',
        'berlaku_mulai',
        'is_active',
        'metode_perhitungan',
        'tenor_bulan',
        'pembulatan',
        'dasar',
        'sumber',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jasa_flat_bulanan' => 'decimal:5',
            'berlaku_mulai' => 'date',
            'is_active' => 'boolean',
            'tenor_bulan' => 'array',
        ];
    }

    public function pinjaman(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'ketentuan_pinjaman_id');
    }
}
