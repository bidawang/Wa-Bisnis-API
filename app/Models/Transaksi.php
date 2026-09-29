<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_tempat', 'id_dompet', 'id_pembayaran', 'jumlah', 'tipe', 'keterangan',
    ];

    protected $casts = ['jumlah' => 'decimal:2'];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function dompet(): BelongsTo
    {
        return $this->belongsTo(Dompet::class, 'id_dompet', 'id_dompet');
    }

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }
}
