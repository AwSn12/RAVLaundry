<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id_order';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_layanan',
        'id_kurir',
        'kode_order',
        'berat_kg',
        'subtotal',
        'ongkir',
        'total_bayar',
        'alamat_pickup',
        'alamat_delivery',
        'tanggal_pickup',
        'jam_pickup',
        'catatan',
        'status_order',
    ];

    protected $casts = [
        'berat_kg' => 'float',
        'subtotal' => 'float',
        'ongkir' => 'float',
        'total_bayar' => 'float',
        'tanggal_pickup' => 'date:Y-m-d',
        'created_at' => 'datetime',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }

    public function kurir()
    {
        return $this->belongsTo(User::class, 'id_kurir', 'id_user');
    }

    public function tracking()
    {
        return $this->hasMany(Tracking::class, 'id_order', 'id_order');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_order', 'id_order');
    }
}
