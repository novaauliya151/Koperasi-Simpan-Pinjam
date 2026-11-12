<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KetentuanSimpanan extends Model
{
    protected $table = 'ketentuan_simpanans';

    protected $fillable = [
        'jenis_simpanan_id',
        'nominal_ketentuan',
        'berlaku_mulai',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nominal_ketentuan' => 'decimal:2',
            'berlaku_mulai' => 'date',
        ];
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(JenisSimpanan::class, 'jenis_simpanan_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
