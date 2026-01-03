<?php
declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function json(array $data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);

        // Basis API headers
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        // Extra headers (optioneel)
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function ok(array $data = [], int $status = 200): void
    {
        self::json([
            'ok' => true,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $meta = []): void
    {
        self::json([
            'ok' => false,
            'error' => [
                'message' => $message,
                'code' => $status,
            ],
            'meta' => (object)$meta,
        ], $status);
    }
}
