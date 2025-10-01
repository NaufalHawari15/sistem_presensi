<?php
// File: app/Models/Position.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan ini

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name'];

    /**
     * Relasi one-to-many ke User.
     * Satu jabatan bisa memiliki banyak pengguna.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}