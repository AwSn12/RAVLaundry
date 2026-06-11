<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false; // Kita handle created_at manual

    protected $fillable = [
        'nama',
        'email',
        'no_telp',
        'alamat',
        'username',
        'password',
        'foto_profil',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_user', 'id_user');
    }

    public function kurirLokasi()
    {
        return $this->hasOne(KurirLokasi::class, 'id_kurir', 'id_user');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isKurir(): bool
    {
        return $this->role === 'KURIR';
    }
}
