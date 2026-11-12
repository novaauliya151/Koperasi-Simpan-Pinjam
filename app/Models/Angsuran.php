<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Angsuran extends Model
{
    protected $table = 'angsurans';
    protected $fillable = ['pinjaman_id', 'urutan', 'jatuh_tempo', 'pokok_tagihan', 'jasa_tagihan', 'status'];
    protected function casts(): array { return ['jatuh_tempo' => 'date', 'pokok_tagihan' => 'decimal:2', 'jasa_tagihan' => 'decimal:2']; }
    public function pinjaman(): BelongsTo { return $this->belongsTo(Pinjaman::class, 'pinjaman_id'); }
    public function alokasi(): HasMany { return $this->hasMany(AlokasiPembayaran::class, 'angsuran_id'); }
}
