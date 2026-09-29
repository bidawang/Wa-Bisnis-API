<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SewaDetail extends Model
{
    protected $table = 'sewa_detail';
    protected $primaryKey = 'id_sewa_detail';

    protected $fillable = [
        'id_sewa', 'id_barang', 'id_paket', 'jumlah', 'harga_satuan',
        'diskon_tipe', 'diskon_nilai', 'diskon_nominal', 'subtotal', 'total', 'catatan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_satuan' => 'decimal:2',
        'diskon_nilai' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sewa(): BelongsTo
    {
        return $this->belongsTo(Sewa::class, 'id_sewa', 'id_sewa');
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
