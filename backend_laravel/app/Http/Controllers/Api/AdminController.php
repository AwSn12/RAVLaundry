<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * GET /api/admin/stats/revenue (auth:sanctum)
     * Statistik pendapatan harian, mingguan, bulanan
     */
    public function revenueStats(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Akses ditolak. Hanya Admin.',
            ], 403);
        }

        $completedStatuses = ['selesai', 'selesai diterima'];

        $orders = Order::whereIn('status_order', $completedStatuses)
            ->get(['total_bayar', 'created_at']);

        $now = now();
        $today = $now->copy()->startOfDay();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();

        $harian = 0;
        $mingguan = 0;
        $bulanan = 0;

        foreach ($orders as $order) {
            $orderDate = $order->created_at;
            if ($orderDate >= $today) {
                $harian += $order->total_bayar;
            }
            if ($orderDate >= $startOfWeek) {
                $mingguan += $order->total_bayar;
            }
            if ($orderDate >= $startOfMonth) {
                $bulanan += $order->total_bayar;
            }
        }

        return response()->json([
            'harian' => $harian,
            'mingguan' => $mingguan,
            'bulanan' => $bulanan,
        ]);
    }
}
