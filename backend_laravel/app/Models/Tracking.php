<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $table = 'tracking';
    protected $primaryKey = 'id_tracking';
    public $timestamps = false;

    protected $fillable = [
        'id_order',
        'status_tracking',
        'waktu_update',
        'keterangan',
    ];

    protected $casts = [
        'waktu_update' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }
}
