<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_tagihan', 'metode', 'jenis', 'jumlah', 'referensi', 'keterangan',
    ];

    protected $casts = ['jumlah' => 'decimal:2'];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan', 'id_tagihan');
    }

    public function transaksi(): HasOne
    {
        return $this->hasOne(Transaksi::class, 'id_pembayaran', 'id_pembayaran');
    }
}
