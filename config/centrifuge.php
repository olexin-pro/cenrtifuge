<?php

declare(strict_types=1);

use RoadRunner\Centrifugo\Request;
use OlexinPro\Centrifuge\Handlers;

return [
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
        Request\Connect::class => Handlers\ConnectHandler::class,
        Request\Subscribe::class => Handlers\SubscribeHandler::class,
        Request\Publish::class => Handlers\PublishHandler::class,
        Request\Refresh::class => Handlers\RefreshHandler::class,
        Request\SubRefresh::class => Handlers\SubRefreshHandler::class,
        Request\RPC::class => Handlers\RpcHandler::class,
    ],
];
