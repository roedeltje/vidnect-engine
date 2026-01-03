<?php
declare(strict_types=1);

// 1) Basis error handling (veilig in productie)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');

// 2) Simple PSR-4 achtige autoloader voor App\
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix) === false) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

// 3) .env laden (optioneel maar handig)
\App\Support\Env::load(dirname(__DIR__) . '/.env');

// 4) Config laden in een globale registry (simpel en uitbreidbaar)
\App\Support\Config::load([
    'app'      => dirname(__DIR__) . '/config/app.php',
    'database' => dirname(__DIR__) . '/config/database.php',
]);
