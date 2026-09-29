<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasukDetail extends Model
{
    protected $table = 'barang_masuk_detail';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_barang_masuk', 'id_barang', 'jumlah', 'harga_beli', 'subtotal', 'kondisi', 'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function barangMasuk(): BelongsTo
    {
        return $this->belongsTo(BarangMasuk::class, 'id_barang_masuk', 'id_barang_masuk');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
