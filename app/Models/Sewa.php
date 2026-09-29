<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sewa extends Model
{
    protected $table = 'sewa';
    protected $primaryKey = 'id_sewa';

    protected $fillable = [
        'id_tempat', 'id_booking', 'kode_sewa', 'google_id', 'nama_penyewa', 'no_hp',
        'tanggal_mulai', 'tanggal_rencana_kembali', 'tanggal_kembali', 'jaminan',
        'catatan', 'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_rencana_kembali' => 'date',
        'tanggal_kembali' => 'date',
    ];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(SewaDetail::class, 'id_sewa', 'id_sewa');
    }

    public function bookingToSewa(): HasOne
    {
        return $this->hasOne(BookingToSewa::class, 'id_sewa', 'id_sewa');
    }

    public function tagihan(): HasOne
    {
        return $this->hasOne(Tagihan::class, 'id_sewa', 'id_sewa');
    }

    public function stokMutasi(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'id_sewa', 'id_sewa');
    }
}
