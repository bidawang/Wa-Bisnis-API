<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaketDetail extends Model
{
    protected $table = 'paket_detail';
    protected $primaryKey = 'id_detail';

    protected $fillable = ['id_paket', 'id_barang', 'jumlah'];

    protected $casts = ['jumlah' => 'integer'];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket', 'id_paket');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
