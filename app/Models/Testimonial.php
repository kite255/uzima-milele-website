<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'title',
        'message',
        'image',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}