<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
    ];
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(User::class);
}

public function workSchedule()
{
    return $this->belongsTo(WorkSchedule::class);
}
}
