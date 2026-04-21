<?php

declare(strict_types=1);

use RoadRunner\Centrifugo\Request;
use OlexinPro\Centrifuge\Handlers;

return [

    /*
    |--------------------------------------------------------------------------
    | Centrifugo API Configuration
    |--------------------------------------------------------------------------
    |
    | api_key:
    | API key used to authenticate requests to the Centrifugo HTTP API.
    |
    | api_url:
    | Base URL of the Centrifugo server.
    |
    | hmac_token:
    | Secret key for HMAC signing (JWT connection tokens).
    |
    | timeout:
    | HTTP request timeout in seconds (used by HttpTransport).
    |
    */
    'api_key'    => env('CENTRIFUGE_API_KEY', ''),
    'api_url'    => env('CENTRIFUGE_API_URL', 'http://localhost:8000'),
    'hmac_token' => env('CENTRIFUGE_HMAC_TOKEN', ''),
    'timeout'    => env('CENTRIFUGE_TIMEOUT', 3),

    /*
    |--------------------------------------------------------------------------
    | Transport
    |--------------------------------------------------------------------------
    |
    | Determines how the package communicates with the Centrifugo server.
    |
    | Supported: "http", "rpc"
    |
    | "rpc" uses RoadRunner's internal Goridge RPC — no extra dependencies
    | required when running under roadrunner-php/laravel-bridge.
    |
    */
    'transport' => env('CENTRIFUGE_TRANSPORT', 'http'),

    /*
    |--------------------------------------------------------------------------
    | RoadRunner RPC Address
    |--------------------------------------------------------------------------
    |
    | Address of the RoadRunner RPC server used by the "rpc" transport.
    |
    */
    'rpc_address' => env('RR_RPC', 'tcp://127.0.0.1:6001'),

    /*
    |--------------------------------------------------------------------------
    | Use Broadcasting Channels
    |--------------------------------------------------------------------------
    |
    | When enabled, uses routes/channels.php for channel authorization.
    | When disabled, uses routes/centrifuge.php.
    |
    */
    'use_broadcasting_channels' => env('CENTRIFUGE_USE_BROADCASTING', false),

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Guards to attempt for authentication in order of priority.
    |
    */
    'guards' => ['sanctum', 'session'],

    /*
    |--------------------------------------------------------------------------
    | RPC Configuration
    |--------------------------------------------------------------------------
    */
    'rpc' => [
        'routes_path' => base_path('routes/rpc.php'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Configuration
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'routes_path' => base_path('routes/centrifuge.php'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Handlers
    |--------------------------------------------------------------------------
    |
    | Mapping of Centrifugo request types to handler classes.
    | Override in your app config or via container bindings.
    |
    */
    'handlers' => [
        Request\Connect::class   => Handlers\ConnectHandler::class,
        Request\Subscribe::class => Handlers\SubscribeHandler::class,
        Request\Publish::class   => Handlers\PublishHandler::class,
        Request\Refresh::class   => Handlers\RefreshHandler::class,
        Request\SubRefresh::class => Handlers\SubRefreshHandler::class,
        Request\RPC::class       => Handlers\RpcHandler::class,
    ],
];
