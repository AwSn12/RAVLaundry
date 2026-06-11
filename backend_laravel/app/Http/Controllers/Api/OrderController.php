<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Layanan;
use App\Models\Tracking;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Format order sesuai Flutter OrderModel
     */
    private function formatOrder(Order $order): array
    {
        return [
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
        ];
    }

    /**
     * GET /api/orders (auth:sanctum)
     * User: hanya order sendiri
     * Admin: semua order
     * Kurir: order dengan status relevan
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with(['layanan', 'tracking' => fn ($q) => $q->orderBy('waktu_update', 'desc')]);

        if ($user->isAdmin()) {
            $query->orderBy('created_at', 'desc');
        } elseif ($user->isKurir()) {
            $kurirStatuses = [
                'menunggu pickup', 'dijemput kurir', 'sedang dicuci',
                'sedang disetrika', 'selesai', 'diantar',
            ];
            $query->whereIn('status_order', $kurirStatuses)->orderBy('created_at', 'desc');
        } else {
            $query->where('id_user', $user->id_user)->orderBy('created_at', 'desc');
        }

        $orders = $query->get();

        return response()->json($orders->map(fn ($o) => $this->formatOrder($o))->toArray());
    }

    /**
     * POST /api/orders (auth:sanctum)
     * Buat order baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_layanan' => 'required|integer|exists:layanan,id_layanan',
            'berat_kg' => 'required|numeric|min:0.1',
            'alamat_pickup' => 'required|string',
            'alamat_delivery' => 'required|string',
            'tanggal_pickup' => 'required|date',
            'jam_pickup' => 'required|string',
            'catatan' => 'nullable|string',
            'metode_pembayaran' => 'nullable|string',
        ], [
            'berat_kg.min' => 'Berat cucian harus lebih dari 0 Kg.',
        ]);

        $layanan = Layanan::findOrFail($request->id_layanan);

        $berat = (float) $request->berat_kg;
        $subtotal = $berat * $layanan->harga_per_kg;
        $ongkir = 0;
        $totalBayar = $subtotal + $ongkir;

        // Generate kode order unik
        $kodeOrder = $this->generateKodeOrder();

        $order = Order::create([
            'id_user' => $request->user()->id_user,
            'id_layanan' => $request->id_layanan,
            'kode_order' => $kodeOrder,
            'berat_kg' => $berat,
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total_bayar' => $totalBayar,
            'alamat_pickup' => $request->alamat_pickup,
            'alamat_delivery' => $request->alamat_delivery,
            'tanggal_pickup' => $request->tanggal_pickup,
            'jam_pickup' => $request->jam_pickup,
            'catatan' => $request->catatan,
            'status_order' => 'menunggu pickup',
        ]);

        // Buat payment record
        Pembayaran::create([
            'id_order' => $order->id_order,
            'metode_pembayaran' => $request->metode_pembayaran ?? 'BAYAR DI TEMPAT',
            'jumlah' => 0,
            'status_pembayaran' => 'belum bayar',
        ]);

        // Initial tracking
        Tracking::create([
            'id_order' => $order->id_order,
            'status_tracking' => 'Order Dibuat',
            'keterangan' => 'Pesanan Anda telah berhasil dibuat dan sedang menunggu kurir.',
        ]);

        // Load relasi untuk response
        $order->load(['layanan', 'tracking' => fn ($q) => $q->orderBy('waktu_update', 'desc')]);

        return response()->json($this->formatOrder($order), 201);
    }

    /**
     * PATCH /api/orders/{id}/status (auth:sanctum)
     * Update status order (Admin/Kurir only)
     */
    public function updateStatus(Request $request, int $id)
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isKurir()) {
            return response()->json([
                'error' => 'Hanya Admin atau Kurir yang dapat mengubah status.',
            ], 403);
        }

        $request->validate([
            'status_order' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status_order' => $request->status_order]);

        // Tambah tracking entry
        Tracking::create([
            'id_order' => $order->id_order,
            'status_tracking' => $request->status_order,
            'keterangan' => $request->keterangan,
        ]);

        // Reload
        $order->load(['layanan', 'tracking' => fn ($q) => $q->orderBy('waktu_update', 'desc')]);

        return response()->json($this->formatOrder($order));
    }

    /**
     * Generate kode order unik (8 karakter alphanumeric)
     */
    private function generateKodeOrder(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $attempts = 0;

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $exists = Order::where('kode_order', $code)->exists();
            $attempts++;
        } while ($exists && $attempts < 10);

        return $code;
    }
}
