<?php

return [
    'host' => env('MQTT_HOST', '103.116.203.74'),
    'port' => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME', 'vending'),
    'password' => env('MQTT_PASSWORD', 'vending61'),
    'client_id' => env('MQTT_CLIENT_ID', 'laravel_mqtt_client_' . uniqid()),
    'use_tls' => env('MQTT_USE_TLS', false),
    'tls_cafile' => env('MQTT_TLS_CAFILE', null), // Path to CA file
    'tls_certfile' => env('MQTT_TLS_CERTFILE', null), // Path to client certificate file
    'tls_keyfile' => env('MQTT_TLS_KEYFILE', null), // Path to client private key file
    'tls_verify_peer' => env('MQTT_TLS_VERIFY_PEER', true),
    'connect_timeout' => env('MQTT_CONNECT_TIMEOUT', 3),
    'socket_timeout' => env('MQTT_SOCKET_TIMEOUT', 5),
    'keep_alive_interval' => env('MQTT_KEEP_ALIVE_INTERVAL', 60),
    'auto_connect' => env('MQTT_AUTO_CONNECT', true), // Whether to automatically connect on application boot
]; 