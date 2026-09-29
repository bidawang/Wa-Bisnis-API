<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dompet extends Model
{
    protected $table = 'dompet';
    protected $primaryKey = 'id_dompet';

    protected $fillable = ['id_tempat', 'saldo'];

    protected $casts = ['saldo' => 'decimal:2'];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_dompet', 'id_dompet');
    }
}
