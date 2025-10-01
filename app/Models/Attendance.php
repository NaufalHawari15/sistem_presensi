<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Disesuaikan dengan file migrasi database.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'office_id',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_photo',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_photo',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_time' => 'datetime', // Mengubah string timestamp menjadi objek Carbon
        'check_out_time' => 'datetime',// Mengubah string timestamp menjadi objek Carbon
    ];

    /**
     * Mendefinisikan relasi bahwa setiap Attendance 'milik' satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendefinisikan relasi bahwa setiap Attendance 'milik' satu Office.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
