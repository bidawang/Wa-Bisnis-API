<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';
    protected $primaryKey = 'id_barang_masuk';

    protected $fillable = [
        'id_tempat', 'kode_masuk', 'supplier', 'total_harga', 'keterangan',
    ];

    protected $casts = ['total_harga' => 'decimal:2'];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(BarangMasukDetail::class, 'id_barang_masuk', 'id_barang_masuk');
    }
}
