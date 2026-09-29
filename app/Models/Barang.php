<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'id_tempat', 'nama', 'satuan', 'jenis', 'harga', 'deskripsi', 'status',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function stok(): HasOne
    {
        return $this->hasOne(BarangStok::class, 'id_barang', 'id_barang');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(BarangFoto::class, 'id_barang', 'id_barang');
    }

    public function tutorial(): HasMany
    {
        return $this->hasMany(BarangTutorial::class, 'id_barang', 'id_barang');
    }

    public function paketDetail(): HasMany
    {
        return $this->hasMany(PaketDetail::class, 'id_barang', 'id_barang');
    }

    public function bookingDetail(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'id_barang', 'id_barang');
    }

    public function sewaDetail(): HasMany
    {
        return $this->hasMany(SewaDetail::class, 'id_barang', 'id_barang');
    }

    public function tagihanDetail(): HasMany
    {
        return $this->hasMany(TagihanDetail::class, 'id_barang', 'id_barang');
    }

    public function stokMutasi(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'id_barang', 'id_barang');
    }

    public function barangMasukDetail(): HasMany
    {
        return $this->hasMany(BarangMasukDetail::class, 'id_barang', 'id_barang');
    }
}
