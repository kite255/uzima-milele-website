<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailCampaignRecipient extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUPPRESSED = 'suppressed';

    protected $fillable = [
        'email_campaign_id',
        'email_subscriber_id',
        'name',
        'email',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'suppressed_at',
        'suppression_reason',
        'tracking_token',
        'first_opened_at',
        'last_opened_at',
        'open_count',
        'first_clicked_at',
        'last_clicked_at',
        'click_count',
        'unsubscribed_at',
        'failure_count',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'suppressed_at' => 'datetime',
            'first_opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'open_count' => 'integer',
            'first_clicked_at' => 'datetime',
            'last_clicked_at' => 'datetime',
            'click_count' => 'integer',
            'unsubscribed_at' => 'datetime',
            'failure_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EmailCampaignRecipient $recipient): void {
            if (blank($recipient->tracking_token)) {
                $recipient->tracking_token = (string) Str::uuid();
            }

            $recipient->open_count ??= 0;
            $recipient->click_count ??= 0;
            $recipient->failure_count ??= 0;
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriber::class, 'email_subscriber_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(EmailCampaignClick::class, 'email_campaign_recipient_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isSuppressed(): bool
    {
        return $this->status === self::STATUS_SUPPRESSED;
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'error_message' => mb_substr($message, 0, 65535),
            'failure_count' => $this->failure_count + 1,
        ]);
    }

    public function markAsSuppressed(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_SUPPRESSED,
            'suppressed_at' => now(),
            'suppression_reason' => $reason,
        ]);
    }

    public function wasOpened(): bool
    {
        return $this->first_opened_at !== null;
    }

    public function markAsOpened(): void
    {
        DB::transaction(function (): void {
            $recipient = self::query()->lockForUpdate()->findOrFail($this->getKey());
            $openedAt = now();

            $recipient->update([
                'first_opened_at' => $recipient->first_opened_at ?? $openedAt,
                'last_opened_at' => $openedAt,
                'open_count' => $recipient->open_count + 1,
            ]);
        });

        $this->refresh();
    }
}
