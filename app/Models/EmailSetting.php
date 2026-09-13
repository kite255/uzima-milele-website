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

    public const DEFAULT_DEVOTION_SEND_TIME =
        '06:00';

    protected $fillable = [
        'auto_schedule_devotions',
        'default_devotion_send_time',
        'default_recipient_scope',
        'email_subscriber_group_id',
    ];

    protected $casts = [
        'auto_schedule_devotions' =>
            'boolean',

        'email_subscriber_group_id' =>
            'integer',
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
            if (
                blank(
                    $settings->default_devotion_send_time
                )
            ) {
                $settings->update([
                    'default_devotion_send_time' =>
                        self::DEFAULT_DEVOTION_SEND_TIME,
                ]);
            }

            return $settings;
        }

        return static::query()
            ->create([
                'auto_schedule_devotions' =>
                    true,

                'default_devotion_send_time' =>
                    self::DEFAULT_DEVOTION_SEND_TIME,

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
    | Recipient Helpers
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

    /*
    |--------------------------------------------------------------------------
    | Devotion Send Time
    |--------------------------------------------------------------------------
    */

    public function devotionSendTime(): string
    {
        return $this->normalizeTime(
            $this->default_devotion_send_time
                ?: self::DEFAULT_DEVOTION_SEND_TIME
        );
    }

    public function devotionSendHour(): int
    {
        [$hour] = explode(
            ':',
            $this->devotionSendTime()
        );

        return (int) $hour;
    }

    public function devotionSendMinute(): int
    {
        [, $minute] = explode(
            ':',
            $this->devotionSendTime()
        );

        return (int) $minute;
    }

    /*
    |--------------------------------------------------------------------------
    | Mutator
    |--------------------------------------------------------------------------
    */

    public function setDefaultDevotionSendTimeAttribute(
        mixed $value
    ): void {
        $this->attributes[
            'default_devotion_send_time'
        ] = $this->normalizeTime(
            $value
                ?: self::DEFAULT_DEVOTION_SEND_TIME
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Time Normalization
    |--------------------------------------------------------------------------
    */

    protected function normalizeTime(
        mixed $value
    ): string {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return self::DEFAULT_DEVOTION_SEND_TIME;
        }

        if (
            preg_match(
                '/^(\d{1,2}):(\d{1,2})$/',
                $value,
                $matches
            )
        ) {
            $hour =
                (int) $matches[1];

            $minute =
                (int) $matches[2];

            if (
                $hour >= 0
                && $hour <= 23
                && $minute >= 0
                && $minute <= 59
            ) {
                return sprintf(
                    '%02d:%02d',
                    $hour,
                    $minute
                );
            }
        }

        return self::DEFAULT_DEVOTION_SEND_TIME;
    }
}