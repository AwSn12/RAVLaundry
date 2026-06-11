<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KurirLokasi;
use Illuminate\Http\Request;

class KurirLokasiController extends Controller
{
    /**
     * POST /api/kurir/lokasi (auth:sanctum)
     * Kurir mengirim lokasi terbaru
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user->isKurir() && !$user->isAdmin()) {
            return response()->json([
                'error' => 'Hanya Kurir yang dapat mengirim lokasi.',
            ], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'id_order' => 'nullable|integer',
        ]);

        $lokasi = KurirLokasi::updateOrCreate(
            ['id_kurir' => $user->id_user],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'id_order' => $request->id_order,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui.',
            'lokasi' => $lokasi,
        ]);
    }

    /**
     * GET /api/kurir/lokasi/{id_kurir}
     * Ambil lokasi kurir by id_kurir (publik — polling dari user)
     */
    public function show(int $id_kurir)
    {
        $lokasi = KurirLokasi::where('id_kurir', $id_kurir)->first();

        if (!$lokasi) {
            return response()->json([
                'error' => 'Lokasi kurir belum tersedia.',
            ], 404);
        }

        return response()->json([
            'id_kurir' => $lokasi->id_kurir,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'id_order' => $lokasi->id_order,
            'updated_at' => $lokasi->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * GET /api/kurir/lokasi-by-order/{id_order}
     * Ambil lokasi kurir by order (publik — polling dari tracking screen)
     */
    public function showByOrder(int $id_order)
    {
        $lokasi = KurirLokasi::where('id_order', $id_order)
            ->orderByDesc('updated_at')
            ->first();

        if (!$lokasi) {
            return response()->json([
                'error' => 'Lokasi kurir belum tersedia.',
            ], 404);
        }

        return response()->json([
            'id_kurir' => $lokasi->id_kurir,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'id_order' => $lokasi->id_order,
            'updated_at' => $lokasi->updated_at?->toIso8601String(),
        ]);
    }
}
