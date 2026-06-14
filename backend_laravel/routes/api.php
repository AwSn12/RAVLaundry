<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LayananController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\KurirLokasiController;
use App\Http\Controllers\Api\MitraLaundryController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes — RAVLaundry
|--------------------------------------------------------------------------
|
| Semua route di file ini otomatis mendapat prefix /api
| Contoh: Route::get('/layanan') → GET /api/layanan
|
*/

// Health check
Route::get('/health', fn () => response()->json(['ok' => true]));

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES (tanpa auth)
// ═══════════════════════════════════════════════════════════════════════════

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Layanan — publik
Route::get('/layanan', [LayananController::class, 'index']);

// Tracking — publik by kode_order
Route::get('/tracking/{kode}', [TrackingController::class, 'show']);

// LBS — Mitra Laundry (publik)
Route::get('/laundry-mitra', [MitraLaundryController::class, 'index']);
Route::get('/laundry-mitra/{id}', [MitraLaundryController::class, 'show']);

// Kurir lokasi — publik read (polling dari user di tracking screen)
Route::get('/kurir/lokasi/{id_kurir}', [KurirLokasiController::class, 'show']);
Route::get('/kurir/lokasi-by-order/{id_order}', [KurirLokasiController::class, 'showByOrder']);


// ═══════════════════════════════════════════════════════════════════════════
// PROTECTED ROUTES (memerlukan Sanctum token)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Kurir update lokasi (hanya kurir/admin)
    Route::post('/kurir/lokasi', [KurirLokasiController::class, 'update']);

    // Admin Stats
    Route::get('/admin/stats/revenue', [AdminController::class, 'revenueStats']);
});
