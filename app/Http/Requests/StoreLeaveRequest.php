<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Leave; // <-- Tambahkan ini

class StoreLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Set ke true agar semua user yang sudah terautentikasi bisa menggunakan request ini.
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'leave_type'    => 'required|string|in:sakit,izin,lainnya',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string',
            'other_reason'  => 'required_if:leave_type,lainnya|nullable|string|max:255',
            'attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        // Menambahkan validasi kustom setelah validasi dasar selesai
        $validator->after(function ($validator) {
            // Panggil method untuk validasi tanggal tumpang tindih (Saran 3A)
            $this->validateDateOverlap($validator);
        });
    }

    /**
     * Validasi untuk mencegah pengajuan pada tanggal yang sudah ada.
     */
    protected function validateDateOverlap($validator)
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $userId = $this->user()->id;

        // Cek apakah ada pengajuan lain yang tumpang tindih
        $existingLeave = Leave::where('user_id', $userId)
            ->whereIn('status', ['Pending', 'Approved']) // Hanya cek yang masih relevan
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    // Cek jika tanggal mulai berada di antara rentang yang sudah ada
                    $q->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $startDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // Cek jika tanggal selesai berada di antara rentang yang sudah ada
                    $q->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $endDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    // Cek jika rentang yang ada berada di dalam rentang baru
                    $q->where('start_date', '>=', $startDate)
                      ->where('end_date', '<=', $endDate);
                });
            })
            ->exists(); // Cukup cek apakah ada atau tidak

        if ($existingLeave) {
            // Jika ada, tambahkan pesan error
            $validator->errors()->add(
                'start_date', 
                'Anda sudah memiliki pengajuan izin pada rentang tanggal tersebut.'
            );
        }
    }

    /**
     * Menangani respons error kustom agar tetap dalam format JSON.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Data yang diberikan tidak valid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}