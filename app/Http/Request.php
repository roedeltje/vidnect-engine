<?php

declare(strict_types=1);

namespace App\Http;

use JsonException;

final class Request
{
    public static function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($_SERVER[$key]) && is_string($_SERVER[$key])) {
            return trim($_SERVER[$key]);
        }

        if (strcasecmp($name, 'Content-Type') === 0 && isset($_SERVER['CONTENT_TYPE'])) {
            return is_string($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : null;
        }

        return null;
    }

    public static function isJsonContentType(): bool
    {
        $ct = self::header('Content-Type');
        return $ct !== null && stripos($ct, 'application/json') === 0;
    }

    public static function requireJsonContentType(): void
    {
        if (!self::isJsonContentType()) {
            Response::json([
                'ok' => false,
                'error' => [
                    'message' => 'Content-Type must be application/json.',
                    'code' => 415,
                ],
            ], 415);
            exit;
        }
    }

    public static function rawBody(int $maxBytes = 1048576): string
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw)) return '';
        return strlen($raw) > $maxBytes ? substr($raw, 0, $maxBytes) : $raw;
    }

    public static function jsonBody(int $maxBytes = 1048576): array
    {
        $raw = trim(self::rawBody($maxBytes));
        if ($raw === '') return [];

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Response::error('Invalid JSON body', 400);
            exit;
        }

        if (!is_array($data)) {
            Response::error('JSON body must be an object or array', 400);
            exit;
        }

        return $data;
    }

    public static function requireJsonBody(int $maxBytes = 1048576): array
    {
        self::requireJsonContentType();
        return self::jsonBody($maxBytes);
    }
}
