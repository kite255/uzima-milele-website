<?php

return [
    'provider' => env('SMS_PROVIDER', 'elive'),

    'sender_id' => env('SMS_SENDER_ID', 'UzimaMilele'),

    'url' => env('SMS_API_URL', 'https://message.elive.co.tz/api/v1/vendor/message/send'),

    'api_key' => env('SMS_API_KEY'),

    'api_secret' => env('SMS_API_SECRET'),
];