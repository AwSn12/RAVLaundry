<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KurirLokasi extends Model
{
    protected $table = 'kurir_lokasi';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_kurir',
        'latitude',
        'longitude',
        'id_order',
        'updated_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'updated_at' => 'datetime',
    ];

    public function kurir()
    {
        return $this->belongsTo(User::class, 'id_kurir', 'id_user');
    }
}
