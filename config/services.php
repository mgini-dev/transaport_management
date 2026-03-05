<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'distancematrix' => [
        'key' => env('DISTANCE_MATRIX_API_KEY'),
        'user_agent' => env('DISTANCE_USER_AGENT', env('APP_NAME', 'NMIS').'/1.0'),
        'nominatim_url' => env('DISTANCE_NOMINATIM_URL', 'https://nominatim.openstreetmap.org'),
        'osrm_url' => env('DISTANCE_OSRM_URL', 'https://router.project-osrm.org'),
    ],

    'system_email' => [
        'enabled' => (bool) env('SYSTEM_EMAIL_ENABLED', false),
        'address' => env('SYSTEM_EMAIL_ADDRESS'),
        'password' => env('SYSTEM_EMAIL_PASSWORD'),
        'from_name' => env('SYSTEM_EMAIL_FROM_NAME', env('APP_NAME', 'NMIS')),
        'smtp' => [
            'host' => env('SYSTEM_EMAIL_SMTP_HOST', env('MAIL_HOST', '127.0.0.1')),
            'port' => (int) env('SYSTEM_EMAIL_SMTP_PORT', env('MAIL_PORT', 2525)),
            'scheme' => env('SYSTEM_EMAIL_SMTP_SCHEME', env('MAIL_SCHEME', 'smtp')),
            'encryption' => env('SYSTEM_EMAIL_SMTP_ENCRYPTION', 'ssl'),
        ],
        'incoming' => [
            'imap' => [
                'host' => env('SYSTEM_EMAIL_IMAP_HOST'),
                'port' => (int) env('SYSTEM_EMAIL_IMAP_PORT', 993),
                'encryption' => env('SYSTEM_EMAIL_IMAP_ENCRYPTION', 'ssl'),
            ],
            'pop' => [
                'host' => env('SYSTEM_EMAIL_POP_HOST'),
                'port' => (int) env('SYSTEM_EMAIL_POP_PORT', 995),
                'encryption' => env('SYSTEM_EMAIL_POP_ENCRYPTION', 'ssl'),
            ],
        ],
    ],

];
