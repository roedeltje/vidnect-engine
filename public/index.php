<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Bootstrap.php';

use App\Http\Router;

$router = new Router();

/**
 * Routes
 */
$router->get('/health', function () {
    $payload = App\Support\Health::check();
    $dbOk = ($payload['checks']['db'] ?? null) === 'pass';
    $statusCode = $dbOk ? 200 : 503;

    App\Http\Response::ok($payload, $statusCode);
});

// API v1
$router->group('/v1', function ($router) {
    $router->get('/health', function () {
        $payload = App\Support\Health::check();
        $dbOk = ($payload['checks']['db'] ?? null) === 'pass';
        $statusCode = $dbOk ? 200 : 503;

        // Optioneel: kleine v1 “meta” wrapper (nu alvast future-proof)
        App\Http\Response::ok([
            'api_version' => 'v1',
            'health' => $payload,
        ], $statusCode);
    });
});

$router->get('/', function () {
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>VidNect Engine</title></head>
          <body style="font-family:system-ui;margin:40px">
          <h1>VidNect Engine</h1><p>Engine is online.</p></body></html>';
});

$router->dispatch();
