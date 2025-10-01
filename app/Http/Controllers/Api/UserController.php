<?php

namespace App\Http\Controllers\Api\Admin; // Contoh namespace untuk Admin

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\AccountActivatedMail; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;   
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Mengaktifkan akun pengguna dan mengirim email notifikasi.
     * Method ini akan dipanggil oleh admin.
     */
    public function activateUser($id)
    {
        $user = User::findOrFail($id);

        // Jika akun sudah aktif, tidak perlu lakukan apa-apa
        if ($user->is_active) {
            return response()->json(['message' => 'Akun ini sudah aktif.'], 409);
        }

        // Ubah status pengguna menjadi aktif
        $user->is_active = true;
        $user->save();

 
        try {
            Mail::to($user->email)->send(new AccountActivatedMail($user));
        } catch (\Exception $e) {
            // Catat log jika email gagal, tapi proses aktivasi tetap dianggap berhasil
            Log::error('Gagal mengirim email aktivasi untuk user ID: ' . $user->id, ['error' => $e->getMessage()]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Akun pengguna berhasil diaktifkan dan notifikasi telah dikirim.',
            'data' => $user,
        ], 200);
    }
}