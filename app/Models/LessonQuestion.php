<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonQuestion extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_CLOSED = 'closed';

    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_HIDDEN = 'hidden';

    protected $fillable = [
        'lesson_id',
        'lesson_topic_id',
        'user_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
        'status',
        'visibility',
        'is_published',
        'instructor_reminded_at',
        'lead_escalated_at',
        'admin_escalated_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'instructor_reminded_at' => 'datetime',
        'lead_escalated_at' => 'datetime',
        'admin_escalated_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function lessonTopic(): BelongsTo
    {
        return $this->belongsTo(LessonTopic::class, 'lesson_topic_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(LessonTopic::class, 'lesson_topic_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function scopeAnswered(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ANSWERED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('visibility', self::VISIBILITY_PRIVATE);
    }

    public function scopeNotHidden(Builder $query): Builder
    {
        return $query->where('visibility', '!=', self::VISIBILITY_HIDDEN);
    }

    public function scopeVisibleToStudent(Builder $query, ?int $userId = null): Builder
    {
        return $query
            ->notHidden()
            ->where(function (Builder $query) use ($userId) {
                if ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhere(function (Builder $query) {
                            $query->answered()->public();
                        });
                } else {
                    $query->answered()->public();
                }
            });
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAnswered(): bool
    {
        return $this->status === self::STATUS_ANSWERED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isPrivate(): bool
    {
        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function isHidden(): bool
    {
        return $this->visibility === self::VISIBILITY_HIDDEN;
    }
}
