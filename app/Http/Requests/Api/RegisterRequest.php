<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Izinkan semua request untuk endpoint registrasi
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:Karyawan,Magang',
            'password' => [
                'required',
                'confirmed', // Memastikan ada field 'password_confirmation' yang cocok
                Password::min(8)
                    ->mixedCase() // Wajib ada huruf besar dan kecil
                    ->numbers()   // Wajib ada angka
                    ->symbols()   // Wajib ada simbol
            ],
        ];
    }

    /**
     * Get the custom validation messages for the defined rules.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah dipakai',
            // PENJELASAN: 'password.default' tidak ada. Pesan error untuk aturan password
            // yang kompleks (mixedCase, numbers, symbols) akan dibuat otomatis oleh Laravel.
            // Jika Anda ingin mengkustomisasinya, Anda harus menentukannya satu per satu,
            // contoh: 'password.mixed' => 'Password harus mengandung huruf besar dan kecil.'
            // Namun, pesan default Laravel biasanya sudah cukup jelas.
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Metode ini akan mengubah format respons error default Laravel
     * menjadi format JSON yang kita inginkan.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $firstErrorMessage = $errors->first();

        // Membuat respons JSON kustom saat validasi gagal
        throw new HttpResponseException(response()->json([
            'message' => $firstErrorMessage,
            'errors' => $errors,
        ], 422)); // 422 Unprocessable Entity adalah status code yang tepat
    }
}
