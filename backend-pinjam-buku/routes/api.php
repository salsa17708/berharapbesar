<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BukuController;
use App\Http\Controllers\API\PeminjamanController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes (memerlukan autentikasi)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Buku - semua user bisa lihat
    Route::get('/buku', [BukuController::class, 'index']);
    Route::get('/buku/{id}', [BukuController::class, 'show']);
    Route::get('/buku/search', [BukuController::class, 'search']);
    
    // Buku - hanya admin & petugas yang bisa create, update, delete
    Route::middleware(['check.role:admin,petugas'])->group(function () {
        Route::post('/buku', [BukuController::class, 'store']);
        Route::put('/buku/{id}', [BukuController::class, 'update']);
        Route::delete('/buku/{id}', [BukuController::class, 'destroy']);
    });
    
    // Peminjaman - user biasa
    Route::post('/pinjam', [PeminjamanController::class, 'pinjam']);
    Route::put('/kembalikan/{id}', [PeminjamanController::class, 'kembalikan']);
    Route::get('/riwayat-saya', [PeminjamanController::class, 'riwayatSaya']);
    
    // Admin & Petugas only
    Route::middleware(['check.role:admin,petugas'])->group(function () {
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::get('/dashboard-stats', [PeminjamanController::class, 'dashboardStats']);
    });

    // Route test untuk cek permission
Route::middleware(['auth:sanctum', 'check.permission:lihat_buku'])->get('/test-permission', function () {
    return response()->json([
        'success' => true,
        'message' => 'Permission works!'
    ]);
});
});