<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagihanDetail extends Model
{
    protected $table = 'tagihan_detail';
    protected $primaryKey = 'id_tagihan_detail';

    protected $fillable = [
        'id_tagihan', 'id_barang', 'id_paket', 'nama_item', 'jumlah',
        'harga_satuan', 'diskon_tipe', 'diskon_nilai', 'diskon_nominal',
        'subtotal', 'total',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_satuan' => 'decimal:2',
        'diskon_nilai' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'id_tagihan', 'id_tagihan');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'id_paket', 'id_paket');
    }
}
