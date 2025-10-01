<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Leave extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'leave_type',   
        'start_date',
        'end_date',
        'reason',       // <-- 'other_reason' sudah dihapus
        'attachment',
        'status',
    ];

    /**
     * Mendefinisikan relasi bahwa setiap pengajuan izin 'milik' satu User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan URL lengkap untuk lampiran.
     *
     * @return string|null
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->attachment) {
            return Storage::url($this->attachment);
        }
        return null;
    }
}
