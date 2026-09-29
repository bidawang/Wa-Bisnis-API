<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCancel extends Model
{
    protected $table = 'booking_cancel';
    protected $primaryKey = 'id_cancel';
    public $timestamps = false;

    protected $fillable = ['id_booking', 'alasan', 'waktu_batal'];

    protected $casts = ['waktu_batal' => 'datetime'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }
}
