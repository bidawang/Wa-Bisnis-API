<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasi';
    protected $primaryKey = 'id_stok_mutasi';
    public $timestamps = false;

    protected $fillable = [
        'id_barang', 'id_booking', 'id_sewa', 'tipe', 'jumlah', 'keterangan', 'created_at',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'created_at' => 'datetime',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    public function sewa(): BelongsTo
    {
        return $this->belongsTo(Sewa::class, 'id_sewa', 'id_sewa');
    }
}
