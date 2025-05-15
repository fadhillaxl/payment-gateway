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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => true,
        'is_3ds' => true,
    ],

    'iot' => [
        'mqtt' => [
            'host' => env('MQTT_HOST', 'localhost'),
            'port' => env('MQTT_PORT', 1883),
            'username' => env('MQTT_USERNAME', ''),
            'password' => env('MQTT_PASSWORD', ''),
            'client_id' => env('MQTT_CLIENT_ID', 'payment-gateway'),
            'clean_session' => env('MQTT_CLEAN_SESSION', true),
            'keep_alive' => env('MQTT_KEEP_ALIVE', 60),
        ],
        'command_topic' => env('MQTT_TOPIC', 'relay/command'),
        'status_topic' => env('MQTT_STATUS_TOPIC', 'relay/status'),
    ],

];
