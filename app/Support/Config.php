<?php
declare(strict_types=1);

namespace App\Support;

final class Config
{
    private static array $items = [];

    public static function load(array $files): void
    {
        foreach ($files as $key => $file) {
            if (is_file($file)) {
                self::$items[$key] = require $file;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // key zoals: "database.host"
        $parts = explode('.', $key);
        $value = self::$items;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }
}
