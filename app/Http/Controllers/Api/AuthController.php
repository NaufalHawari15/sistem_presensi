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
     * Tahap 1 Registrasi: Validasi data & kirim OTP.
     * Pengguna diizinkan memilih role awal (sementara).
     */
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $otp = random_int(100000, 999999);
        $verificationToken = Str::random(60);
        $cacheKey = 'registration_token_' . $verificationToken;

        // Simpan data sementara di cache, termasuk 'role' pilihan pengguna.
        Cache::put($cacheKey, [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'otp' => $otp,
        ], now()->addMinutes(10));

        try {
            Mail::to($validatedData['email'])->send(new OtpMail($otp));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email OTP registrasi.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal mengirim email verifikasi.'], 500);
        }

         return response()->json([
            'message' => 'Registrasi berhasil! Silakan cek email untuk kode verifikasi.',
            'status' => 'sukses',
            'registerResult' => [
                'email' => $validatedData['email'],
                'namaPelanggan' => $validatedData['name'],
                'token_verifikasi' => $verificationToken,
                'otp' => (string)$otp, // OTP disertakan untuk kemudahan testing
            ]
        ], 200);
    }

    /**
     * Tahap 2: Verifikasi OTP & buat user baru dengan status TIDAK AKTIF.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|min:6|max:6']);
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token verifikasi tidak ditemukan.'], 401);
        }

        $cacheKey = 'registration_token_' . $token;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['otp'] != $request->otp) {
            return response()->json(['message' => 'Kode OTP tidak valid atau token telah kedaluwarsa.'], 422);
        }

        // Buat user baru. Kolom 'is_active' akan otomatis 'false'
        // sesuai default di database migration Anda.
        $user = User::create([
            'name' => $cachedData['name'],
            'email' => $cachedData['email'],
            'password' => $cachedData['password'],
            'role' => $cachedData['role'],
        ]);

        Cache::forget($cacheKey);

        return response()->json([
            'status' => 'success',
            'message' => 'Verifikasi berhasil. Akun Anda sedang menunggu persetujuan admin.',
            'data' => $user
        ], 201);
    }

    /**
     * Kirim ulang OTP jika diperlukan.
     */
    public function resendOtp(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token verifikasi tidak ditemukan.'], 401);
        }

        $cacheKey = 'registration_token_' . $token;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData) {
            return response()->json(['message' => 'Token verifikasi tidak valid atau telah kedaluwarsa.'], 400);
        }

        $newOtp = random_int(100000, 999999);
        $cachedData['otp'] = $newOtp;
        Cache::put($cacheKey, $cachedData, now()->addMinutes(10));

        try {
            Mail::to($cachedData['email'])->send(new OtpMail($newOtp));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim ulang email OTP.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal mengirim ulang email verifikasi.'], 500);
        }

        return response()->json([
            'status' => 'sukses',
            'message' => 'Kode OTP berhasil dikirim ulang.',
            'data' => [
                'token_verifikasi' => $token,
            ]
        ], 200);
    }

    /**
     * Menangani login pengguna, dengan pengecekan status aktif.
     */
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json(['message' => 'Email atau Password yang diberikan salah.'], 401);
        }

        // Pengecekan status aktif. Ini adalah bagian penting dari alur kerja baru.
        if (!$user->is_active) {
            return response()->json(['message' => 'Akun Anda belum aktif. Silakan hubungi administrator.'], 403); // 403 Forbidden
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

    /**
     * Menangani logout pengguna.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success', 'message' => 'Logout berhasil'], 200);
    }
}

