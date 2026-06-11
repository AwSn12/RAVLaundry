<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = false;

    protected $fillable = [
        'id_order',
        'metode_pembayaran',
        'jumlah',
        'bukti_transfer',
        'status_pembayaran',
        'tanggal_bayar',
    ];

    protected $casts = [
        'jumlah' => 'float',
        'tanggal_bayar' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }
}
