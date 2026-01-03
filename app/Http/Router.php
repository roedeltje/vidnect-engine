<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Response;

final class Router
{
    private array $routes = [];
    private string $currentPrefix = '';

    public function get(string $path, callable $handler): void
    {
        $fullPath = $this->normalize($this->currentPrefix . $path);
        $this->routes['GET'][$fullPath] = $handler;
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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $this->normalize($path);

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            Response::error('Not Found', 404);
            return;
        }

        $handler();
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
