<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiController;

// ... (komentar lainnya) ...

// =================================================================
// RUTE PUBLIK (Tidak Perlu Login)
// =================================================================
Route::post('/register', [AuthController::class, 'register']);

// [DIPERBARUI] Satu rute login untuk semua role
Route::post('/login', [AuthController::class, 'login']);


// =================================================================
// RUTE YANG DILINDUNGI (Wajib Login & Mengirim Token)
// =================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute dari ApiController Anda
    Route::get('/offices', [ApiController::class, 'getOffices']);
    Route::post('/absen', [ApiController::class, 'submitAttendance']);
    Route::get('/absensi/riwayat', [ApiController::class, 'getAttendanceHistory']);
    Route::post('/izin', [ApiController::class, 'submitLeave']);
    Route::get('/izin/riwayat', [ApiController::class, 'getLeaveHistory']);
});