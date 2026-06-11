<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;

class TrackingController extends Controller
{
    /**
     * GET /api/tracking/{kode}
     * Public tracking by kode_order
     */
    public function show(string $kode)
    {
        $order = Order::with([
            'layanan',
            'tracking' => fn ($q) => $q->orderBy('waktu_update', 'desc'),
        ])->where('kode_order', $kode)->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order tidak ditemukan. Periksa kembali kode order Anda.',
            ], 404);
        }

        return response()->json([
            'id_order' => $order->id_order,
            'id_user' => $order->id_user,
            'id_layanan' => $order->id_layanan,
            'id_kurir' => $order->id_kurir,
            'kode_order' => $order->kode_order,
            'berat_kg' => $order->berat_kg,
            'subtotal' => $order->subtotal,
            'ongkir' => $order->ongkir,
            'total_bayar' => $order->total_bayar,
            'alamat_pickup' => $order->alamat_pickup,
            'alamat_delivery' => $order->alamat_delivery,
            'tanggal_pickup' => $order->tanggal_pickup?->format('Y-m-d'),
            'jam_pickup' => $order->jam_pickup,
            'catatan' => $order->catatan,
            'status_order' => $order->status_order,
            'created_at' => $order->created_at?->toIso8601String(),
            'layanan' => $order->layanan ? [
                'id_layanan' => $order->layanan->id_layanan,
                'nama_layanan' => $order->layanan->nama_layanan,
                'deskripsi' => $order->layanan->deskripsi,
                'harga_per_kg' => $order->layanan->harga_per_kg,
                'estimasi_hari' => $order->layanan->estimasi_hari,
                'foto_layanan' => $order->layanan->foto_layanan,
            ] : null,
            'tracking' => $order->tracking->map(fn ($t) => [
                'id_tracking' => $t->id_tracking,
                'id_order' => $t->id_order,
                'status_tracking' => $t->status_tracking,
                'waktu_update' => $t->waktu_update?->toIso8601String(),
                'keterangan' => $t->keterangan,
            ])->toArray(),
        ]);
    }
}
