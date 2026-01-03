<?php

declare(strict_types=1);

namespace App\Http;

final class Router
{
    private array $routes = [];
    private string $currentPrefix = '';

    public function get(string $path, callable $handler): void
    {
        $fullPath = $this->normalize($this->currentPrefix . $path);
        $this->routes['GET'][$fullPath] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $fullPath = $this->normalize($this->currentPrefix . $path);
        $this->routes['POST'][$fullPath] = $handler;
    }

    public function group(string $prefix, callable $callback): void
    {
        $previous = $this->currentPrefix;

        $prefix = '/' . trim($prefix, '/');
        $this->currentPrefix = $this->normalize($previous . $prefix);

        $callback($this);

        $this->currentPrefix = $previous;
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $this->normalize((string)$path);

        // 1) Exact match
        $handler = $this->routes[$method][$path] ?? null;
        $params  = [];

        // 2) Param match fallback: /v1/rooms/{id}
        if (!$handler && isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $routeHandler) {
                if (strpos($routePath, '{') === false) {
                    continue;
                }

                // Escape everything, then turn \{param\} into a capture group
                $escaped = preg_quote($routePath, '#');
                $pattern = preg_replace('#\\\\\{[^/]+\\\\\}#', '([^/]+)', $escaped);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches);
                    $handler = $routeHandler;
                    $params = $matches;
                    break;
                }
            }
        }

        if (!$handler) {
            Response::error('Not Found', 404);
            return;
        }

        $handler(...$params);
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
