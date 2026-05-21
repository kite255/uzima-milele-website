<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

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
    |
    | This is the default sender address for all system emails.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'no-reply@uzimamilele.or.tz'),
        'name' => env('MAIL_FROM_NAME', 'Uzima Milele Ministry'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uzima Milele System Email Addresses
    |--------------------------------------------------------------------------
    |
    | These addresses are used by the system for specific notification flows.
    |
    */

    'system_address' => env('SYSTEM_EMAIL', 'no-reply@uzimamilele.or.tz'),

    'support_address' => env('SUPPORT_EMAIL', 'info@uzimamilele.or.tz'),

    /*
    |--------------------------------------------------------------------------
    | Prayer Request Email Addresses
    |--------------------------------------------------------------------------
    |
    | Add one or many emails in .env like this:
    |
    | PRAYER_REQUEST_EMAILS=maombi@uzimamilele.or.tz,info@uzimamilele.or.tz
    |
    */

    'prayer_addresses' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('PRAYER_REQUEST_EMAILS', 'maombi@uzimamilele.or.tz'))
    ), function ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    })),

];