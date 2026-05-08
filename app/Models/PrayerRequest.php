<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerRequest extends Model
{
    protected $fillable = [
        'name',
        'contact',
        'prayer_type',
        'message',
        'is_private',
        'status',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];
}