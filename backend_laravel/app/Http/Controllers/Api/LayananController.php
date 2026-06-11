<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;

class LayananController extends Controller
{
    /**
     * GET /api/layanan
     * Return semua layanan laundry
     */
    public function index()
    {
        $layanan = Layanan::orderBy('id_layanan', 'asc')->get();

        return response()->json($layanan->map(fn ($l) => [
            'id_layanan' => $l->id_layanan,
            'nama_layanan' => $l->nama_layanan,
            'deskripsi' => $l->deskripsi,
            'harga_per_kg' => $l->harga_per_kg,
            'estimasi_hari' => $l->estimasi_hari,
            'foto_layanan' => $l->foto_layanan,
        ]));
    }
}
