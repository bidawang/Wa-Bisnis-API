<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $table = 'booking';
    protected $primaryKey = 'id_booking';

    protected $fillable = [
        'id_tempat', 'kode_booking', 'google_id', 'nama', 'no_hp',
        'tanggal_mulai', 'tanggal_selesai', 'catatan', 'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'id_booking', 'id_booking');
    }

    public function cancel(): HasMany
    {
        return $this->hasMany(BookingCancel::class, 'id_booking', 'id_booking');
    }

    public function sewa(): HasOne
    {
        return $this->hasOne(Sewa::class, 'id_booking', 'id_booking');
    }

    public function bookingToSewa(): HasOne
    {
        return $this->hasOne(BookingToSewa::class, 'id_booking', 'id_booking');
    }

    public function tagihan(): HasOne
    {
        return $this->hasOne(Tagihan::class, 'id_booking', 'id_booking');
    }

    public function stokMutasi(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'id_booking', 'id_booking');
    }
}
