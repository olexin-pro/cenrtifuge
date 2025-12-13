<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Routing;

final class ChannelRouter
{
    private array $routes = [];

    public function channel(string $pattern, \Closure $callback): void
    {
        $this->routes[$pattern] = [
            'pattern' => $this->compilePattern($pattern),
            'callback' => $callback,
            'parameters' => $this->extractParameterNames($pattern),
        ];
    }

    public function resolve(string $channel): ?\Closure
    {
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $channel)) {
                return $route['callback'];
            }
        }

        return null;
    }

    public function extractParameters(string $channel): array
    {
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $channel, $matches)) {
                $parameters = [];

                foreach ($route['parameters'] as $index => $name) {
                    $parameters[$name] = $matches[$index + 1] ?? null;
                }

                return array_values($parameters);
            }
        }

        return [];
    }

    private function compilePattern(string $pattern): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '([^.]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    private function extractParameterNames(string $pattern): array
    {
        preg_match_all('/\{(\w+)\}/', $pattern, $matches);
        return $matches[1] ?? [];
    }
}
