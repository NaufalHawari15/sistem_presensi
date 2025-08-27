<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menangani registrasi pengguna baru untuk semua role.
     */
    public function register(Request $request)
    {
        // ... (Fungsi register ini tidak perlu diubah, sudah benar)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:employee,intern',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Silakan login.',
            'data' => $user
        ], 201);
    }

    /**
     * [DIPERBARUI] Menangani login untuk semua jenis pengguna (Karyawan/Magang).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verifikasi pengguna dan password. Tidak perlu cek role di sini.
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau Password yang diberikan salah.'],
            ]);
        }
        
        // Hapus token lama dan buat yang baru
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