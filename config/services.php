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
        'merchant_id' => env('MIDTRANS_MERCHANT_ID', 'G974380638'),
        'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-JPQuVZQ2c0_PuXsX'),
        'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-AZkHf6HGM1J5admjxavrGoX9'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'sanitize' => env('MIDTRANS_SANITIZE', true),
        '3ds' => env('MIDTRANS_3DS', true),
    ],

    'iot' => [
        'mqtt' => [
            'host' => env('MQTT_HOST', '103.116.203.74'),
            'port' => env('MQTT_PORT', 1883),
            'username' => env('MQTT_USERNAME', 'vending'),
            'password' => env('MQTT_PASSWORD', 'vending61'),
            'client_id' => env('MQTT_CLIENT_ID', 'payment-gateway'),
            'clean_session' => env('MQTT_CLEAN_SESSION', true),
            'keep_alive' => env('MQTT_KEEP_ALIVE', 60),
        ],
        'command_topic' => env('MQTT_TOPIC', 'relay/command'),
        'status_topic' => env('MQTT_STATUS_TOPIC', 'relay/status'),
    ],

];
