<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TempatSewa extends Model
{
    protected $table = 'tempat_sewa';
    protected $primaryKey = 'id_tempat';

    protected $fillable = [
        'id_user', 'nama_tempat', 'alamat', 'no_hp', 'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'id_tempat', 'id_tempat');
    }

    public function paket(): HasMany
    {
        return $this->hasMany(Paket::class, 'id_tempat', 'id_tempat');
    }

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class, 'id_tempat', 'id_tempat');
    }

    public function sewa(): HasMany
    {
        return $this->hasMany(Sewa::class, 'id_tempat', 'id_tempat');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'id_tempat', 'id_tempat');
    }

    public function dompet(): HasOne
    {
        return $this->hasOne(Dompet::class, 'id_tempat', 'id_tempat');
    }

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'id_tempat', 'id_tempat');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_tempat', 'id_tempat');
    }
}
