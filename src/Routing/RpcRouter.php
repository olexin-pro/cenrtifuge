<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Routing;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use RoadRunner\Centrifugo\Request\RPC;

final class RpcRouter
{
    private array $routes = [];
    private array $middleware = [];
    private array $groupStack = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function middleware(array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    public function register(string $method, string $handler): void
    {
        $fullMethod = $this->buildFullMethod($method);

        $this->routes[$fullMethod] = [
            'handler' => $handler,
            'middleware' => $this->collectMiddleware(),
        ];
    }

    public function group(string $prefix, array $middleware, \Closure $callback): void
    {
        $this->groupStack[] = compact('prefix', 'middleware');

        $callback($this);

        array_pop($this->groupStack);
    }

    /**
     * @throws BindingResolutionException
     */
    public function dispatch(string $method, array $data, ?Authenticatable $user, RPC $request): mixed
    {
        $route = $this->routes[$method] ?? throw new \RuntimeException("RPC method not found: {$method}");

        $next = fn() => $this->executeHandler($route['handler'], $data, $user, $request);

        foreach (array_reverse($route['middleware']) as $middlewareClass) {
            $next = $this->wrapWithMiddleware($middlewareClass, $request, $user, $data, $next);
        }

        return $next();
    }

    private function wrapWithMiddleware(
        string $middlewareClass,
        RPC $request,
        ?Authenticatable $user,
        array $data,
        \Closure $next
    ): \Closure {
        return fn() => $this->container
            ->make($middlewareClass)
            ->handle($request, $user, $data, $next);
    }

    /**
     * @throws BindingResolutionException
     */
    private function executeHandler(string $handlerClass, array $data, ?Authenticatable $user, RPC $request): mixed
    {
        return $this->container->make($handlerClass)->handle($data, $user, $request);
    }

    private function collectMiddleware(): array
    {
        $middleware = $this->middleware;

        foreach ($this->groupStack as $group) {
            $middleware = array_merge($middleware, $group['middleware']);
        }

        return $middleware;
    }

    private function buildFullMethod(string $method): string
    {
        $parts = [];

        foreach ($this->groupStack as $group) {
            if ($group['prefix'] !== '') {
                $parts[] = $group['prefix'];
            }
        }

        $parts[] = $method;

        return implode('.', $parts);
    }
}
