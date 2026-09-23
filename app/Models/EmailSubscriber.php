<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'status',
        'unsubscribe_token',
        'subscribed_at',
        'unsubscribed_at',
        'source',
        'language',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EmailSubscriber $subscriber) {
            if (filled($subscriber->email)) {
                $subscriber->email = Str::lower(trim($subscriber->email));
            }

            if (blank($subscriber->name) && (filled($subscriber->first_name) || filled($subscriber->last_name))) {
                $subscriber->name = static::buildFullName(
                    $subscriber->first_name,
                    $subscriber->last_name
                );
            }

            if (blank($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }

            if (blank($subscriber->subscribed_at)) {
                $subscriber->subscribed_at = now();
            }

            if (blank($subscriber->status)) {
                $subscriber->status = 'subscribed';
            }

            if (blank($subscriber->source)) {
                $subscriber->source = 'website';
            }

            if (blank($subscriber->language)) {
                $subscriber->language = 'sw';
            }
        });

        static::updating(function (EmailSubscriber $subscriber) {
            if (filled($subscriber->email)) {
                $subscriber->email = Str::lower(trim($subscriber->email));
            }

            if ($subscriber->isDirty('first_name') || $subscriber->isDirty('last_name')) {
                $subscriber->name = static::buildFullName(
                    $subscriber->first_name,
                    $subscriber->last_name
                );
            }

            if (blank($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
        });
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            EmailSubscriberGroup::class,
            'email_subscriber_group_members',
            'email_subscriber_id',
            'email_subscriber_group_id'
        )->withTimestamps();
    }

    public function suppressions(): HasMany
    {
        return $this->hasMany(EmailSuppression::class, 'email', 'email');
    }

    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed');
    }

    public function getFullNameAttribute(): string
    {
        return static::buildFullName($this->first_name, $this->last_name);
    }

    protected static function buildFullName(?string $firstName, ?string $lastName): string
    {
        return trim(
            collect([$firstName, $lastName])
                ->filter(fn ($value) => filled($value))
                ->implode(' ')
        );
    }
}
