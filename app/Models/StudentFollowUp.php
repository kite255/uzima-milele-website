<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFollowUp extends Model
{
    protected $fillable = [
        'lesson_enrollment_id',
        'instructor_id',
        'contact_method',
        'outcome',
        'note',
        'next_follow_up_at',
    ];

    protected function casts(): array
    {
        return [
            'next_follow_up_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(
            LessonEnrollment::class,
            'lesson_enrollment_id'
        );
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'instructor_id'
        );
    }
}