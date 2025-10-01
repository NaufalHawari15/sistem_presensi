<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Menggunakan Auth facade
use Carbon\Carbon; // Menggunakan Carbon

class AttendanceController extends Controller
{
    /**
     * Mencatat waktu absen masuk (Clock In) dengan validasi lokasi dan status.
     */
    public function clockIn(Request $request)
    {
        // Validasi input dasar dari request
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|image|max:2048', // Validasi file adalah gambar dan maks 2MB
        ]);

        $user = Auth::user(); // Mengambil pengguna yang sedang login
        $office = $user->office; // Mengambil kantor dari relasi pengguna

        // Validasi #1: Pastikan pengguna terhubung ke sebuah kantor
        if (!$office) {
            return response()->json(['message' => 'Profil Anda belum diatur untuk kantor manapun.'], 422);
        }

        // Cek apakah pengguna sudah absen masuk hari ini
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', Carbon::today($office->timezone ?? 'Asia/Jakarta'))
            ->first();

        if ($existingAttendance) {
            return response()->json(['message' => 'Anda sudah melakukan absen masuk hari ini.'], 409); // 409 Conflict
        }

        // Validasi #2: Validasi jarak lokasi pengguna
        $distance = $this->calculateDistance($office->latitude, $office->longitude, $request->latitude, $request->longitude);
        if ($distance > $office->radius) {
            return response()->json(['message' => 'Anda berada di luar radius lokasi kantor yang diizinkan.'], 422);
        }

        // Validasi #3: Tentukan status "On Time" atau "Late"
        $workSchedule = $office->workSchedule;
        if (!$workSchedule) {
            return response()->json(['message' => 'Kantor Anda belum memiliki jadwal kerja yang valid.'], 422);
        }

        $timezone = 'Asia/Jakarta';
        $currentTime = Carbon::now($timezone);
        $startTime = Carbon::createFromTimeString($workSchedule->start_time, $timezone);
        $deadline = $startTime->addMinutes($workSchedule->late_tolerance_minutes);
        $status = $currentTime->isAfter($deadline) ? 'Late' : 'On Time';

        // Proses penyimpanan foto
        $photoPath = $request->file('photo')->store('check_in_photos', 'public');

        // Simpan data absensi baru dengan semua data yang sudah divalidasi
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'office_id' => $office->id, // Mengambil office_id dari relasi, lebih aman
            'check_in_time' => Carbon::now(), // Disimpan dalam UTC
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'check_in_photo' => $photoPath,
            'status' => $status, // Menyimpan status yang sudah dihitung
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Absen masuk berhasil dicatat. Status Anda: ' . $status,
            'data' => $attendance,
        ], 201);
    }

    /**
     * Mencatat waktu absen pulang (Clock Out).
     */
    public function clockOut(Request $request)
    {
        // ... (Kode clockOut Anda tidak saya ubah karena sudah cukup baik)
        $request->validate([
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'photo' => 'required|image',
        ]);

        $user = $request->user();
        $today = Carbon::today('Asia/Jakarta');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', $today)
            ->first();

        if (!$attendance) {
            return response()->json(
                [
                    'message' => 'Anda harus melakukan absen masuk terlebih dahulu.'
                ], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json(
                [
                    'message' => 'Anda sudah melakukan absen pulang hari ini.'
                ], 409);
        }

        $photoPath = $request->file('photo')->store('check_out_photos', 'public');

        $attendance->update([
            'check_out_time' => now(),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'check_out_photo' => $photoPath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Absen pulang berhasil dicatat.',
            'data' => $attendance->fresh(),
        ], 200);
    }

    /**
     * Mengambil riwayat absensi pengguna.
     */
     public function getHistory(Request $request)
    {
        $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'bulan'   => 'nullable|integer|between:1,12',
            'tahun'   => 'nullable|integer|min:2020',
        ]);

        $user = $request->user();
        $query = Attendance::where('user_id', $user->id);

        // =====> PERBAIKAN LOGIKA ADA DI SINI <=====

        // Filter berdasarkan tanggal spesifik
        if ($request->filled('tanggal')) {
            $query->whereDate('check_in_time', $request->tanggal);
        }

        // Filter berdasarkan bulan
        if ($request->filled('bulan')) {
            $query->whereMonth('check_in_time', $request->bulan);
        }

        // Filter berdasarkan tahun
        if ($request->filled('tahun')) {
            $query->whereYear('check_in_time', $request->tahun);
        }

        // ==========================================

        $history = $query->orderBy('check_in_time', 'desc')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Riwayat absensi berhasil diambil.',
            'data'    => $history,
        ]);
    }

    /**
     * Menghitung jarak antara dua koordinat dalam meter.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
}

