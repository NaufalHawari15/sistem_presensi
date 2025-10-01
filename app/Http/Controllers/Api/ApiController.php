<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Menangani tahap 1 registrasi: validasi data dan pengiriman OTP.
     */
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $otp = random_int(100000, 999999);
        $verificationToken = Str::random(60);

        // [MODIFIKASI] Menggunakan token sebagai kunci cache
        $cacheKey = 'registration_token_' . $verificationToken;
        Cache::put($cacheKey, [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'otp' => $otp,
        ], now()->addMinutes(10)); // OTP berlaku selama 10 menit

        try {
            Mail::to($validatedData['email'])->send(new OtPmail($otp));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email OTP registrasi.', [
                'email_tujuan' => $validatedData['email'],
                'pesan_error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email verifikasi.',
            ], 500);
        }

        return response()->json([
            'message' => 'Registrasi berhasil! Silakan cek email untuk kode verifikasi.',
            'status' => 'sukses',
            'registerResult' => [
                'email' => $validatedData['email'],
                'namaPelanggan' => $validatedData['name'],
                'token_verifikasi' => $verificationToken,
                'otp' => (string)$otp,
            ]
        ], 200);
    }

    /**
     * [MODIFIKASI] Menangani verifikasi OTP menggunakan Bearer Token.
     * Endpoint: POST /api/verify-otp
     * Header: Authorization: Bearer {token_verifikasi}
     * Body: { "otp": "123456" }
     */
    public function verifyOtp(Request $request)
    {
        // 1. Validasi OTP dari body request
        $request->validate([
            'otp' => 'required|string|min:6|max:6',
        ]);

        // 2. Ambil token dari Authorization header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token verifikasi tidak ditemukan di header.',
            ], 401); // 401 Unauthorized
        }

        // 3. Cari data di cache menggunakan token
        $cacheKey = 'registration_token_' . $token;
        $cachedData = Cache::get($cacheKey);

        // 4. Cocokkan OTP dari request dengan yang ada di cache
        if (!$cachedData || $cachedData['otp'] != $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP tidak valid atau token telah kedaluwarsa.',
            ], 422);
        }

        // 5. Jika cocok, buat user baru
        $user = User::create([
            'name' => $cachedData['name'],
            'email' => $cachedData['email'],
            'password' => $cachedData['password'],
            'role' => $cachedData['role'],
        ]);

        // Hapus data dari cache setelah berhasil digunakan
        Cache::forget($cacheKey);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Anda sekarang bisa login.',
            'data' => $user
        ], 201);
    }

    // ... (Fungsi login dan logout tidak perlu diubah)
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau Password yang diberikan salah.',
            ], 401);
        }
        
        $user->tokens()->delete();
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ], 200);
    }
}
