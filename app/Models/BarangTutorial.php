<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangTutorial extends Model
{
    protected $table = 'barang_tutorial';
    protected $primaryKey = 'id_tutorial';

    protected $fillable = [
        'id_barang', 'judul', 'deskripsi', 'video_url', 'konten', 'urutan', 'status',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
