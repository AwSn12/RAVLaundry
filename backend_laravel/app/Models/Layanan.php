<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';
    public $timestamps = false;

    protected $fillable = [
        'nama_layanan',
        'deskripsi',
        'harga_per_kg',
        'estimasi_hari',
        'foto_layanan',
    ];

    protected $casts = [
        'harga_per_kg' => 'float',
        'estimasi_hari' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'id_layanan', 'id_layanan');
    }
}
