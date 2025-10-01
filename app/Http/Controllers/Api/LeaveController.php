<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    /**
     * API untuk pengajuan Izin
     */
    public function izin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required|date_format:Y-m-d',
            'end_date'      => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'reason'        => 'required|string|max:500',
            'attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            }

            $leave = Leave::create([
                'user_id'    => $request->user()->id,
                'leave_type' => 'Izin',
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'reason'     => $request->reason,
                'attachment' => $attachmentPath,
                'status'     => 'Pending',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan izin berhasil.',
                'data' => $leave,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal membuat izin: ' . $e->getMessage());
            return response()->json([
                'status' => 
                'error', 
                'message' => 
                'Server error.'
            ], 500);
        }
    }

    /**
     * API untuk pengajuan Sakit
    **/
    public function sakit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required|date_format:Y-m-d', // untuk tanggal sakit 
            'reason'        => 'required|string|max:500',
            'attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            }

            $leave = Leave::create([
                'user_id'    => $request->user()->id,
                'leave_type' => 'Sakit',
                'start_date' => $request->start_date,
                'end_date'   => $request->start_date, // sama dengan tanggal sakit
                'reason'     => $request->reason,
                'attachment' => $attachmentPath,
                'status'     => 'Pending',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan sakit berhasil.',
                'data' => $leave,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal membuat sakit: ' . $e->getMessage());
            return response()->json([
                'status' => 
                'error', 'message' => 
                'Server error.'
            ], 500);
        }
    }

    /**
     * API untuk pengajuan Cuti
     */
    public function cuti(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required|date_format:Y-m-d',
            'end_date'      => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'reason'        => 'required|string|max:500',
            'leave_type'    => 'required|string|in:Cuti Tahunan,Cuti Melahirkan,Cuti Lainnya',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $leave = Leave::create([
                'user_id'    => $request->user()->id,
                'leave_type' => $request->leave_type, // jenis cuti
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'reason'     => $request->reason,
                'status'     => 'Pending',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan cuti berhasil.',
                'data' => $leave,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal membuat cuti: ' . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 
                'Server error.'
            ], 500);
        }
    }

    /**
    * Menampilkan riwayat pengajuan izin/sakit/cuti user
    */
    public function history(Request $request)
    {
        try {
            $leaves = Leave::where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Riwayat pengajuan berhasil diambil.',
                'data'    => $leaves,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil riwayat pengajuan: '.$e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        }
    }
}
