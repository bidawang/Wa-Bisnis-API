<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    protected $table = 'tagihan';
    protected $primaryKey = 'id_tagihan';

    protected $fillable = [
        'id_tempat', 'id_booking', 'id_sewa', 'kode_tagihan', 'subtotal',
        'diskon_tipe', 'diskon_nilai', 'diskon_nominal', 'biaya_tambahan',
        'total', 'dibayar', 'sisa', 'status', 'catatan',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'diskon_nilai' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'biaya_tambahan' => 'decimal:2',
        'total' => 'decimal:2',
        'dibayar' => 'decimal:2',
        'sisa' => 'decimal:2',
    ];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    public function sewa(): BelongsTo
    {
        return $this->belongsTo(Sewa::class, 'id_sewa', 'id_sewa');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(TagihanDetail::class, 'id_tagihan', 'id_tagihan');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_tagihan', 'id_tagihan');
    }
}
