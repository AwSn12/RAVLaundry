<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MitraLaundryController extends Controller
{
    /**
     * GET /api/laundry-mitra
     * 
     * Catatan: Tabel mitra_laundry belum ada di database.sql utama.
     * Jika tabel belum ada, return array kosong.
     * Anda bisa menambahkan migration untuk tabel ini nanti.
     */
    public function index()
    {
        try {
            $mitraList = \DB::table('mitra_laundry')->orderBy('id', 'asc')->get();
            return response()->json($mitraList);
        } catch (\Exception $e) {
            // Tabel belum ada — return kosong
            return response()->json([]);
        }
    }

    /**
     * GET /api/laundry-mitra/{id}
     */
    public function show(int $id)
    {
        try {
            $mitra = \DB::table('mitra_laundry')->where('id', $id)->first();

            if (!$mitra) {
                return response()->json([
                    'error' => 'Mitra laundry tidak ditemukan.',
                ], 404);
            }

            return response()->json($mitra);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Mitra laundry tidak ditemukan.',
            ], 404);
        }
    }
}
