<?php
// File: app/Models/Department.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Tambahkan ini

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name'];

    /**
     * Relasi one-to-many ke User.
     * Satu departemen bisa memiliki banyak pengguna.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}