<?php
// File: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /**
     * Menggunakan trait yang diperlukan untuk otentikasi, API, dan fitur bawaan Laravel.
     */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     * Disesuaikan agar sinkron dengan UserResource.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Role sangat penting untuk membedakan pengguna (employee/intern)
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi (misalnya, saat dikirim sebagai JSON).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang tipe datanya harus di-cast secara otomatis.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Selalu pastikan password di-hash
        ];
    }

    /**
     * Relasi one-to-many ke data Absensi.
     * Seorang pengguna bisa memiliki banyak catatan absensi.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relasi one-to-many ke data Izin/Cuti.
     * Seorang pengguna bisa memiliki banyak catatan pengajuan izin.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }
}