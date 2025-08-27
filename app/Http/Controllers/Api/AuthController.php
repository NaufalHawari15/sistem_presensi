<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // [BARU] Import Log Facade
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Menangani tahap 1 registrasi: validasi data dan pengiriman OTP.
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:employee,intern',
        ]);

        $otp = Str::random(6);

        Cache::put('otp_' . $validatedData['email'], [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'otp' => $otp,
        ], now()->addMinutes(10));

        try {
            Mail::to($validatedData['email'])->send(new OtpMail($otp));
        } catch (\Exception $e) {
            // [DIPERBARUI] Mencatat log dengan format yang lebih terstruktur dan informatif
            Log::error('Gagal mengirim email OTP saat registrasi.', [
                'email_tujuan' => $validatedData['email'],
                'pesan_error' => $e->getMessage(),
                'file' => $e->getFile(),
                'baris' => $e->getLine(),
                // 'trace' => $e->getTraceAsString() // Uncomment baris ini jika butuh trace lengkap
            ]);

            // Mengembalikan respons error yang ramah untuk pengguna
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email OTP. Pastikan konfigurasi email Anda benar.',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP telah dikirim ke email Anda. Silakan verifikasi untuk menyelesaikan registrasi.',
        ], 200);
    }

    /**
     * Menangani tahap 2 registrasi: verifikasi OTP dan pembuatan user.
     */
    public function verifyOtp(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        $cacheKey = 'otp_' . $validatedData['email'];
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['otp'] !== $validatedData['otp']) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak valid atau telah kedaluwarsa.',
            ], 422);
        }

        $user = User::create([
            'name' => $cachedData['name'],
            'email' => $cachedData['email'],
            'password' => $cachedData['password'],
            'role' => $cachedData['role'],
        ]);

        Cache::forget($cacheKey);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Silakan login.',
            'data' => $user
        ], 201);
    }


    /**
     * Menangani login untuk semua jenis pengguna (Karyawan/Magang).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
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

    /**
     * Menangani logout untuk semua jenis pengguna.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ], 200);
    }
}
