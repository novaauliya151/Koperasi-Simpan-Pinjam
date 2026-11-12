<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiPembayaran extends Model
{
    protected $table = 'alokasi_pembayarans';
    protected $fillable = ['pembayaran_id', 'angsuran_id', 'alokasi_pokok', 'alokasi_jasa'];
    protected function casts(): array { return ['alokasi_pokok' => 'decimal:2', 'alokasi_jasa' => 'decimal:2']; }
    public function pembayaran(): BelongsTo { return $this->belongsTo(Pembayaran::class, 'pembayaran_id'); }
    public function angsuran(): BelongsTo { return $this->belongsTo(Angsuran::class, 'angsuran_id'); }
}
