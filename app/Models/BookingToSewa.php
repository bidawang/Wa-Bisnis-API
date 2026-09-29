<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingToSewa extends Model
{
    protected $table = 'booking_to_sewa';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id_booking', 'id_sewa', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    public function sewa(): BelongsTo
    {
        return $this->belongsTo(Sewa::class, 'id_sewa', 'id_sewa');
    }
}
