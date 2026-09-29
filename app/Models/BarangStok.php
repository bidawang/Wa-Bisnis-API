<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangStok extends Model
{
    protected $table = 'barang_stok';
    protected $primaryKey = 'id_barang_stok';
    public $timestamps = false;

    protected $fillable = [
        'id_barang', 'jumlah_total', 'jumlah_tersedia', 'jumlah_dipesan',
        'jumlah_disewa', 'jumlah_rusak', 'jumlah_hilang', 'updated_at',
    ];

    protected $casts = [
        'jumlah_total' => 'integer',
        'jumlah_tersedia' => 'integer',
        'jumlah_dipesan' => 'integer',
        'jumlah_disewa' => 'integer',
        'jumlah_rusak' => 'integer',
        'jumlah_hilang' => 'integer',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
