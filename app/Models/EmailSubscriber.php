<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            /*
            |--------------------------------------------------------------------------
            | Full Name
            |--------------------------------------------------------------------------
            */
            if (
                blank($subscriber->name) &&
                (
                    filled($subscriber->first_name) ||
                    filled($subscriber->last_name)
                )
            ) {
                $subscriber->name = static::buildFullName(
                    $subscriber->first_name,
                    $subscriber->last_name
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Unsubscribe Token
            |--------------------------------------------------------------------------
            */
            if (blank($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }

            /*
            |--------------------------------------------------------------------------
            | Subscription Date
            |--------------------------------------------------------------------------
            */
            if (blank($subscriber->subscribed_at)) {
                $subscriber->subscribed_at = now();
            }

            /*
            |--------------------------------------------------------------------------
            | Default Status
            |--------------------------------------------------------------------------
            */
            if (blank($subscriber->status)) {
                $subscriber->status = 'subscribed';
            }

            /*
            |--------------------------------------------------------------------------
            | Default Source
            |--------------------------------------------------------------------------
            */
            if (blank($subscriber->source)) {
                $subscriber->source = 'website';
            }

            /*
            |--------------------------------------------------------------------------
            | Default Language
            |--------------------------------------------------------------------------
            */
            if (blank($subscriber->language)) {
                $subscriber->language = 'sw';
            }
        });

        static::updating(function (EmailSubscriber $subscriber) {
            /*
            |--------------------------------------------------------------------------
            | Keep Full Name Synchronized
            |--------------------------------------------------------------------------
            */
            if (
                $subscriber->isDirty('first_name') ||
                $subscriber->isDirty('last_name')
            ) {
                $subscriber->name = static::buildFullName(
                    $subscriber->first_name,
                    $subscriber->last_name
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Protect Unsubscribe Token
            |--------------------------------------------------------------------------
            */
            if (blank($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(
            EmailCampaignRecipient::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSubscribed($query)
    {
        return $query->where(
            'status',
            'subscribed'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Helpers
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return static::buildFullName(
            $this->first_name,
            $this->last_name
        );
    }

    protected static function buildFullName(
        ?string $firstName,
        ?string $lastName
    ): string {
        return trim(
            collect([
                $firstName,
                $lastName,
            ])
                ->filter(fn ($value) => filled($value))
                ->implode(' ')
        );
    }
}