<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paket extends Model
{
    protected $table = 'paket';
    protected $primaryKey = 'id_paket';

    protected $fillable = [
        'id_tempat', 'nama_paket', 'deskripsi', 'harga_normal', 'harga_paket',
        'diskon', 'status', 'foto',
    ];

    protected $casts = [
        'harga_normal' => 'decimal:2',
        'harga_paket' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    public function tempatSewa(): BelongsTo
    {
        return $this->belongsTo(TempatSewa::class, 'id_tempat', 'id_tempat');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PaketDetail::class, 'id_paket', 'id_paket');
    }

    public function bookingDetail(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'id_paket', 'id_paket');
    }

    public function sewaDetail(): HasMany
    {
        return $this->hasMany(SewaDetail::class, 'id_paket', 'id_paket');
    }

    public function tagihanDetail(): HasMany
    {
        return $this->hasMany(TagihanDetail::class, 'id_paket', 'id_paket');
    }
}
