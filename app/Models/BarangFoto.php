<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangFoto extends Model
{
    protected $table = 'barang_foto';
    protected $primaryKey = 'id_foto';

    protected $fillable = ['id_barang', 'foto', 'is_utama', 'urutan'];

    protected $casts = [
        'is_utama' => 'boolean',
        'urutan' => 'integer',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
