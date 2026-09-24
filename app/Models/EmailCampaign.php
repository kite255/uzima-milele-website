<?php

namespace App\Models;

use App\Services\Email\CampaignAuditService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory;

    public const TYPE_DEVOTION = 'devotion';
    public const TYPE_CUSTOM = 'custom';

    public const RECIPIENT_SCOPE_SUBSCRIBED = 'subscribed';
    public const RECIPIENT_SCOPE_SELECTED = 'selected';
    public const RECIPIENT_SCOPE_GROUP = 'group';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'name',
        'type',
        'devotion_id',
        'subject',
        'content',
        'recipient_scope',
        'email_subscriber_group_id',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'scheduled_at',
        'queued_at',
        'sent_at',
        'paused_at',
        'cancelled_at',
        'completed_at',
        'parent_campaign_id',
        'audience_filter_type',
        'audience_filter_value',
        'template_id',
        'last_batch_sent_at',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::created(function (EmailCampaign $campaign): void {
            app(CampaignAuditService::class)->record(
                $campaign,
                'created',
                null,
                [],
                auth()->id()
            );
        });

        static::updated(function (EmailCampaign $campaign): void {
            if (! $campaign->wasChanged('status')) {
                return;
            }

            $action = match ($campaign->status) {
                self::STATUS_QUEUED => 'queued',
                self::STATUS_SCHEDULED => 'scheduled',
                default => null,
            };

            if ($action === null) {
                return;
            }

            app(CampaignAuditService::class)->record(
                $campaign,
                $action,
                null,
                [],
                auth()->id()
            );
        });
    }

    protected function casts(): array
    {
        return [
            'total_recipients' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'scheduled_at' => 'datetime',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'paused_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_batch_sent_at' => 'datetime',
        ];
    }

    public function devotion(): BelongsTo
    {
        return $this->belongsTo(Devotion::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    public function pendingRecipients(): HasMany
    {
        return $this->recipients()->where('status', EmailCampaignRecipient::STATUS_PENDING);
    }

    public function targetSubscribers(): BelongsToMany
    {
        return $this->belongsToMany(
            EmailSubscriber::class,
            'email_campaign_targets',
            'email_campaign_id',
            'email_subscriber_id'
        )->withTimestamps();
    }

    public function subscriberGroup(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriberGroup::class, 'email_subscriber_group_id');
    }

    public function parentCampaign(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_campaign_id');
    }

    public function childCampaigns(): HasMany
    {
        return $this->hasMany(self::class, 'parent_campaign_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignTemplate::class, 'template_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(EmailCampaignActivityLog::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(EmailCampaignClick::class);
    }

    public function isDevotion(): bool
    {
        return $this->type === self::TYPE_DEVOTION;
    }

    public function isCustom(): bool
    {
        return $this->type === self::TYPE_CUSTOM;
    }

    public function sendsToAllSubscribers(): bool
    {
        return $this->recipient_scope === self::RECIPIENT_SCOPE_SUBSCRIBED;
    }

    public function sendsToSelectedSubscribers(): bool
    {
        return $this->recipient_scope === self::RECIPIENT_SCOPE_SELECTED;
    }

    public function sendsToSubscriberGroup(): bool
    {
        return $this->recipient_scope === self::RECIPIENT_SCOPE_GROUP;
    }

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

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCompletedHistory(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_SENT], true);
    }

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

    public function isDueForSending(): bool
    {
        if (! $this->isScheduled() || ! $this->scheduled_at) {
            return false;
        }

        return $this->scheduled_at->lte(now());
    }

    public function openedRecipientsCount(): int
    {
        return $this->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->whereNotNull('first_opened_at')
            ->count();
    }

    public function unopenedRecipientsCount(): int
    {
        return $this->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->whereNull('first_opened_at')
            ->count();
    }

    public function openRate(): float
    {
        $sentRecipients = $this->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->count();

        if ($sentRecipients === 0) {
            return 0.0;
        }

        $openedRecipients = $this->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->whereNotNull('first_opened_at')
            ->count();

        return round(($openedRecipients / $sentRecipients) * 100, 1);
    }
}
