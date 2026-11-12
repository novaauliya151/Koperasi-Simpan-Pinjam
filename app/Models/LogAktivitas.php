<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    protected $fillable = ['user_id', 'aksi', 'subjek_tipe', 'subjek_id', 'alasan', 'perubahan', 'terjadi_pada'];
    protected function casts(): array { return ['perubahan' => 'array', 'terjadi_pada' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
