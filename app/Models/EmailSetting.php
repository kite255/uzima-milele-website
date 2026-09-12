<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSetting extends Model
{
    public const RECIPIENT_SCOPE_SUBSCRIBED =
        'subscribed';

    public const RECIPIENT_SCOPE_GROUP =
        'group';

    protected $fillable = [
        'auto_schedule_devotions',
        'default_devotion_send_time',
        'default_recipient_scope',
        'email_subscriber_group_id',
    ];

    protected $casts = [
        'auto_schedule_devotions' =>
            'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Current Settings
    |--------------------------------------------------------------------------
    */

    public static function current(): self
    {
        $settings = static::query()
            ->first();

        if ($settings) {
            return $settings;
        }

        return static::query()
            ->create([
                'auto_schedule_devotions' =>
                    true,

                'default_devotion_send_time' =>
                    '06:00',

                'default_recipient_scope' =>
                    self::RECIPIENT_SCOPE_SUBSCRIBED,

                'email_subscriber_group_id' =>
                    null,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function subscriberGroup(): BelongsTo
    {
        return $this->belongsTo(
            EmailSubscriberGroup::class,
            'email_subscriber_group_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function usesAllSubscribers(): bool
    {
        return $this->default_recipient_scope
            === self::RECIPIENT_SCOPE_SUBSCRIBED;
    }

    public function usesSubscriberGroup(): bool
    {
        return $this->default_recipient_scope
            === self::RECIPIENT_SCOPE_GROUP;
    }
}