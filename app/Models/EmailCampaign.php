<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Campaign Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_DEVOTION = 'devotion';

    public const TYPE_CUSTOM = 'custom';

    /*
    |--------------------------------------------------------------------------
    | Campaign Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'type',
        'devotion_id',
        'subject',
        'content',
        'recipient_scope',
        'status',

        'total_recipients',
        'sent_count',
        'failed_count',

        'scheduled_at',
        'queued_at',
        'sent_at',

        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'total_recipients' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',

            'scheduled_at' => 'datetime',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function devotion(): BelongsTo
    {
        return $this->belongsTo(
            Devotion::class
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(
            EmailCampaignRecipient::class
        );
    }

    public function pendingRecipients(): HasMany
    {
        return $this->recipients()
            ->where(
                'status',
                EmailCampaignRecipient::STATUS_PENDING
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Campaign Type Helpers
    |--------------------------------------------------------------------------
    */

    public function isDevotion(): bool
    {
        return $this->type === self::TYPE_DEVOTION;
    }

    public function isCustom(): bool
    {
        return $this->type === self::TYPE_CUSTOM;
    }

    /*
    |--------------------------------------------------------------------------
    | Campaign Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }

    public function isSending(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /*
    |--------------------------------------------------------------------------
    | Sending Rules
    |--------------------------------------------------------------------------
    */

    public function canSend(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canSchedule(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canCancelSchedule(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    /*
    |--------------------------------------------------------------------------
    | Scheduled Campaign
    |--------------------------------------------------------------------------
    */

    public function isDueForSending(): bool
    {
        if (! $this->isScheduled()) {
            return false;
        }

        if (! $this->scheduled_at) {
            return false;
        }

        return $this->scheduled_at->lte(now());
    }
}