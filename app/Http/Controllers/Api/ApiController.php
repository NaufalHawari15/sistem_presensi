<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApiController extends Controller
{
    /**
     * Mengambil daftar semua lokasi kantor yang aktif.
     * Endpoint ini tidak memerlukan otentikasi.
     */
    public function getOffices()
    {
        $offices = Office::select('id', 'name', 'latitude', 'longitude', 'radius')->get();
        return response()->json([
            'status' => 'success',
            'data' => $offices
        ]);
    }

    /**
     * Menerima dan memproses data absensi (check-in & check-out) dari pengguna.
     */
    public function submitAttendance(Request $request)
    {
        $validated = $request->validate([
            'attendance_type' => 'required|in:check-in,check-out',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|image|max:5120', // Maksimal 5MB
            'office_id' => 'required|exists:offices,id',
        ]);

        $user = $request->user();
        $today = Carbon::today();
        $attendanceType = $validated['attendance_type'];

        // Logika untuk Check-in
        if ($attendanceType === 'check-in') {
            // Cek apakah sudah ada absensi masuk hari ini untuk mencegah duplikasi
            $existingAttendance = Attendance::where('user_id', $user->id)->whereDate('check_in_time', $today)->first();
            if ($existingAttendance) {
                return response()->json(['status' => 'error', 'message' => 'Anda sudah melakukan absensi masuk hari ini.'], 422);
            }

            $photoPath = $request->file('photo')->store('attendance-photos', 'public');
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'office_id' => $validated['office_id'],
                'check_in_time' => now(),
                'check_in_latitude' => $validated['latitude'],
                'check_in_longitude' => $validated['longitude'],
                'check_in_photo' => $photoPath,
                'status' => 'On Time', // Logika status bisa dikembangkan lebih lanjut
            ]);

            return response()->json(['status' => 'success', 'message' => 'Check-in berhasil dicatat.', 'data' => $attendance], 201);
        }

        // Logika untuk Check-out
        if ($attendanceType === 'check-out') {
            // Cari data check-in hari ini yang belum ada check-out nya
            $attendance = Attendance::where('user_id', $user->id)->whereDate('check_in_time', $today)->whereNull('check_out_time')->first();
            if (!$attendance) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ditemukan data check-in untuk hari ini atau Anda sudah check-out.'], 422);
            }

            $photoPath = $request->file('photo')->store('attendance-photos', 'public');
            $attendance->update([
                'check_out_time' => now(),
                'check_out_latitude' => $validated['latitude'],
                'check_out_longitude' => $validated['longitude'],
                'check_out_photo' => $photoPath,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Check-out berhasil dicatat.', 'data' => $attendance]);
        }
    }

    /**
     * Mengambil riwayat absensi untuk pengguna yang sedang login.
     * Menggunakan eager loading dan pagination untuk efisiensi.
     */
    public function getAttendanceHistory()
    {
        // `with('office:id,name')` adalah eager loading untuk menghindari N+1 problem
        $attendances = Auth::user()->attendances()
                            ->with('office:id,name')
                            ->orderBy('check_in_time', 'desc')
                            ->paginate(15);

        return response()->json($attendances);
    }

    /**
     * Menerima pengajuan izin dari pengguna.
     */
    public function submitLeave(Request $request)
    {
        $validatedData = $request->validate([
            'leave_type' => 'required|in:Sakit,Izin,Lainnya',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required_if:leave_type,Sakit,Izin|string|max:255',
            'other_reason' => 'required_if:leave_type,Lainnya|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Maksimal 2MB
        ]);

        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('leave-attachments', 'public')
            : null;

        // Menggunakan array spread operator (...) untuk mengisi data dari hasil validasi secara efisien
        $leave = Leave::create([
            'user_id' => Auth::id(),
            'status' => 'Pending',
            'attachment' => $attachmentPath,
            ...$validatedData
        ]);

        return response()->json(['status' => 'success', 'message' => 'Pengajuan izin berhasil dikirim.', 'data' => $leave], 201);
    }

    /**
     * Mengambil riwayat pengajuan izin untuk pengguna yang sedang login.
     */
    public function getLeaveHistory()
    {
        // Menggunakan pagination untuk membatasi jumlah data yang dikirim per request
        $leaves = Auth::user()->leaves()
                        ->orderBy('start_date', 'desc')
                        ->paginate(15);

        return response()->json($leaves);
    }
}