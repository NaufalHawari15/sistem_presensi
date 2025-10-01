<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller untuk Pengguna Biasa
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;

// 1. PASTIKAN USER CONTROLLER UNTUK ADMIN SUDAH DI-IMPORT
use App\Http\Controllers\Api\Admin\UserController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK (Tidak perlu diubah) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login'])->name('login');


// --- RUTE UNTUK PENGGUNA BIASA YANG SUDAH LOGIN ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rute Absensi
    Route::post('/absensi/masuk', [AttendanceController::class, 'clockIn']);
    Route::post('/absensi/pulang', [AttendanceController::class, 'clockOut']);
    Route::get('/absensi/riwayat', [AttendanceController::class, 'getHistory']);

    // Rute Izin (Leaves)
    // Route::post('/leaves', [LeaveController::class, 'store']);
    Route::post('/izin', [LeaveController::class, 'izin']);
    Route::post('/sakit', [LeaveController::class, 'sakit']);
    Route::post('/cuti', [LeaveController::class, 'cuti']);

    // Riwayat pengajuan izin user
Route::get('/leaves/history', [LeaveController::class, 'history']);
});


// 2. BUAT GRUP TERPISAH UNTUK RUTE ADMIN
// Grup ini juga dilindungi oleh auth:sanctum karena admin juga harus login.
Route::middleware('auth:sanctum')->prefix('admin')->name('admin.')->group(function () {

    // Endpoint: POST /api/admin/users/{id}/activate
    Route::post('/users/{id}/activate', [UserController::class, 'activateUser'])->name('users.activate');

    // Anda bisa menambahkan rute admin lainnya di sini di masa depan
    // contoh: Route::get('/users', [UserController::class, 'getAllUsers'])->name('users.list');

});
