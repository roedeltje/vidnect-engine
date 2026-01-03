<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . $path;

// Als het echt bestand/map is, laat PHP server het zelf serveren
if ($path !== '/' && file_exists($file)) {
    return false;
}

// Anders naar de front controller
require __DIR__ . '/index.php';
