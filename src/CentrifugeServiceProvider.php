<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use OlexinPro\Cenrtifuge\Auth\BroadcastingAccessControl;
use OlexinPro\Cenrtifuge\Auth\MultiGuardAuthenticator;
use OlexinPro\Centrifuge\Auth\RoutingAccessControl;
use OlexinPro\Centrifuge\Contracts\Authenticator;
use OlexinPro\Centrifuge\Contracts\ChannelAccessControl;
use OlexinPro\Centrifuge\Routing\ChannelRouter;
use OlexinPro\Centrifuge\Routing\RpcRouter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class CentrifugeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/centrifuge.php',
            'centrifuge'
        );

        $this->registerRouters();
        $this->registerAuthenticator();
        $this->registerAccessControl();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/centrifuge.php' => config_path('centrifuge.php'),
        ], 'centrifuge-config');

        $this->publishes([
            __DIR__ . '/../stubs/routes/rpc.php' => base_path('routes/rpc.php'),
            __DIR__ . '/../stubs/routes/centrifuge.php' => base_path('routes/centrifuge.php'),
        ], 'centrifuge-routes');

        $this->loadRoutes();
    }

    private function registerRouters(): void
    {
        $this->app->singleton(RpcRouter::class);
        $this->app->singleton(ChannelRouter::class);
    }

    private function registerAuthenticator(): void
    {
        $this->app->singleton(Authenticator::class, function (Application $app) {
            return new MultiGuardAuthenticator(
                auth: $app->make('auth'),
                guards: $app->get('config')->get('centrifuge.guards', ['sanctum', 'session'])
            );
        });
    }

    private function registerAccessControl(): void
    {
        $this->app->singleton(ChannelAccessControl::class, function (Application $app) {
            $useBroadcasting = $app->get('config')->get('centrifuge.use_broadcasting_channels', false);

            if ($useBroadcasting) {
                return new BroadcastingAccessControl(
                    $app->make('Illuminate\Broadcasting\BroadcastManager')
                );
            }

            return new RoutingAccessControl(
                $app->make(ChannelRouter::class)
            );
        });
    }

    private function loadRoutes(): void
    {
        $this->loadRpcRoutes();
        $this->loadChannelRoutes();
    }

    private function loadRpcRoutes(): void
    {
        $routesPath = $this->app->get('config')->get('centrifuge.rpc.routes_path');

        if (!file_exists($routesPath)) {
            return;
        }

        $rpc = $this->app->make(RpcRouter::class);
        require $routesPath;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws BindingResolutionException
     */
    private function loadChannelRoutes(): void
    {
        if ($this->app->get('config')->get('centrifuge.use_broadcasting_channels', false)) {
            return;
        }

        $routesPath = $this->app->get('config')->get('centrifuge.channels.routes_path');

        if (!file_exists($routesPath)) {
            return;
        }

        $centrifuge = $this->app->make(ChannelRouter::class);
        require $routesPath;
    }
}
