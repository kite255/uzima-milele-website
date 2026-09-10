<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'google_id',
        'facebook_id',
        'avatar',
        'ministry_name',
        'ministry_bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filament Admin Access
    |--------------------------------------------------------------------------
    | Admins can access everything.
    | Instructors can access /admin but resources are restricted by each Resource.
    | Students cannot access /admin.
    */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'instructor']);
    }

    public function getFilamentName(): string
    {
        return $this->name ?: 'Admin';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Lesson / Instructor Relationships
    |--------------------------------------------------------------------------
    */

    // Legacy relationship kept for compatibility with the existing
    // lessons.instructor_id field.
    public function instructorLessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'instructor_id');
    }

    // Lessons where this instructor is the lead/supervising instructor.
    public function ledLessons(): HasMany
    {
        return $this->hasMany(
            Lesson::class,
            'lead_instructor_id'
        );
    }

    // Lessons where this instructor belongs to the follow-up team.
    public function followUpLessons(): BelongsToMany
    {
        return $this->belongsToMany(
            Lesson::class,
            'lesson_follow_up_instructor',
            'instructor_id',
            'lesson_id'
        )->withTimestamps();
    }

    // Student enrollments currently assigned to this instructor.
    public function assignedLessonEnrollments(): HasMany
    {
        return $this->hasMany(
            LessonEnrollment::class,
            'follow_up_instructor_id'
        );
    }

    public function lessonEnrollments(): HasMany
    {
        return $this->hasMany(LessonEnrollment::class);
    }

    public function enrolledLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_enrollments')
            ->withPivot([
                'enrolled_at',
                'follow_up_instructor_id',
                'instructor_assigned_at',
            ])
            ->withTimestamps();
    }
}
