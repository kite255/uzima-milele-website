<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Campaign Sending Throttle
    |--------------------------------------------------------------------------
    |
    | cPanel mail servers can temporarily reject or discard mail when too many
    | failures/deferrals happen in a short period. Campaigns are therefore sent
    | in conservative batches with a pause between each batch.
    |
    */

    'campaign_batch_size' => (int) env('MAIL_CAMPAIGN_BATCH_SIZE', 20),

    'campaign_batch_delay_minutes' => (int) env(
        'MAIL_CAMPAIGN_BATCH_DELAY_MINUTES',
        10
    ),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)
            ),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@uzimamilele.or.tz'),
        'name' => env('MAIL_FROM_NAME', 'Uzima Milele Ministry'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uzima Milele System Email Addresses
    |--------------------------------------------------------------------------
    */

    'system_address' => env('SYSTEM_EMAIL', 'no-reply@uzimamilele.or.tz'),

    'support_address' => env('SUPPORT_EMAIL', 'info@uzimamilele.or.tz'),

    /*
    |--------------------------------------------------------------------------
    | Prayer Request Email Addresses
    |--------------------------------------------------------------------------
    */

    'prayer_addresses' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('PRAYER_REQUEST_EMAILS', 'maombi@uzimamilele.or.tz'))
    ), function ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    })),

];
