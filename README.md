# Centrifuge for RoadRunner Laravel Bridge

> **EXPERIMENTAL**
> This package is an experiment and a work in progress. Use it at your own risk. The API may change without notice and there are no stability guarantees.

Laravel package for integrating [Centrifugo](https://centrifugal.dev/) WebSocket server with [RoadRunner](https://roadrunner.dev/) via the `roadrunner-php/laravel-bridge`.

## Requirements

- PHP 8.3+
- Laravel 10, 11, or 12
- RoadRunner with `roadrunner-php/laravel-bridge` ^6.0
- `roadrunner-php/centrifugo` ^2.2

## Installation

```bash
composer require olexin-pro/cenrtifuge
```

Publish the config and route stubs:

```bash
php artisan vendor:publish --tag=centrifuge-config
php artisan vendor:publish --tag=centrifuge-routes
```

## Configuration

Config file: `config/centrifuge.php`

| Key | Default | Description |
|-----|---------|-------------|
| `use_broadcasting_channels` | `false` | Use `routes/channels.php` (Laravel Broadcasting) instead of `routes/centrifuge.php` |
| `guards` | `['sanctum', 'session']` | Auth guards tried in order during connection |
| `rpc.routes_path` | `routes/rpc.php` | Path to RPC routes file |
| `channels.routes_path` | `routes/centrifuge.php` | Path to channel routes file |
| `handlers` | see below | Map of Centrifugo request types to handler classes |

### Environment variables

```env
CENTRIFUGE_USE_BROADCASTING=false
```

## RoadRunner Worker

Register `CentrifugoWorker` in your RoadRunner configuration:

```yaml
# .rr.yaml
centrifuge:
  proxy_connect_timeout: 1s
  # ...
```

Point RoadRunner to `CentrifugoWorker::class` as the worker implementation in your bootstrap.

## Channel Authorization

By default, channels are authorized via `routes/centrifuge.php`:

```php
use Illuminate\Contracts\Auth\Authenticatable;
use OlexinPro\Centrifuge\Routing\ChannelRouter;

/** @var ChannelRouter $centrifuge */

// Private channel with a parameter
$centrifuge->channel('private-user.{userId}', function (?Authenticatable $user, string $userId) {
    return $user && (int) $user->getAuthIdentifier() === (int) $userId;
});

// Public channel
$centrifuge->channel('public.chat', function () {
    return true;
});
```

To use Laravel's standard `routes/channels.php` instead, set `CENTRIFUGE_USE_BROADCASTING=true`.

## RPC

Register RPC handlers in `routes/rpc.php`:

```php
use OlexinPro\Centrifuge\Routing\RpcRouter;

/** @var RpcRouter $rpc */

// Global middleware
$rpc->middleware([\App\Centrifuge\Middleware\LogRpcRequest::class]);

// Single method
$rpc->register('ping', \App\Centrifuge\Handlers\PingHandler::class);

// Grouped methods with middleware
$rpc->group('', [\App\Centrifuge\Middleware\RequireAuth::class], function (RpcRouter $rpc) {
    $rpc->group('posts', [], function (RpcRouter $rpc) {
        $rpc->register('create', \App\Centrifuge\Handlers\CreatePostHandler::class);
    });
});
```

Registered method name for nested groups is built as `prefix.method`, e.g. `posts.create`.

### Implementing an RPC handler

```php
use Illuminate\Contracts\Auth\Authenticatable;
use OlexinPro\Centrifuge\Contracts\RpcHandlerContract;
use RoadRunner\Centrifugo\Request\RPC;

class PingHandler implements RpcHandlerContract
{
    public function handle(array $data, ?Authenticatable $user, RPC $request): mixed
    {
        return ['pong' => true];
    }
}
```

## Custom Handlers

Override any built-in request handler by rebinding it in `config/centrifuge.php`:

```php
'handlers' => [
    \RoadRunner\Centrifugo\Request\Connect::class   => \App\Centrifuge\Handlers\MyConnectHandler::class,
    \RoadRunner\Centrifugo\Request\Subscribe::class => \OlexinPro\Centrifuge\Handlers\SubscribeHandler::class,
    // ...
],
```

All handler classes must implement `OlexinPro\Centrifuge\Contracts\RequestHandler`.

## License

MIT
