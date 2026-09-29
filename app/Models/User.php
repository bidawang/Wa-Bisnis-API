<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens; // 1. TAMBAHKAN IMPORT INI

class User extends Authenticatable
{
    use HasApiTokens; // 2. TAMBAHKAN TRAIT INI DI DALAM CLASS
    protected $table = 'user';
    protected $primaryKey = 'id_user';
    public $timestamps = true;

    protected $fillable = [
        'google_id', 'email', 'status', 'password', 'nama', 'no_hp', 'role', 'foto', 'alamat',
    ];

    public function tempatSewa(): HasMany
    {
        return $this->hasMany(TempatSewa::class, 'id_user', 'id_user');
    }
}
