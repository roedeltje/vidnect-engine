<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Bootstrap.php';

use App\Http\Response;
use App\Http\Router;
use App\Support\DB;
use App\Support\Health;
use App\Repositories\RoomRepository;
use App\Repositories\RoomTokenRepository;
use App\Handlers\V1\RoomsHandler;
use App\Handlers\V1\TokensHandler;

$router = new Router();

/**
 * Root health (non-versioned)
 */
$router->get('/health', function (): void {
    $payload = Health::check();
    $dbOk = ($payload['checks']['db'] ?? null) === 'pass';
    $statusCode = $dbOk ? 200 : 503;

    Response::ok($payload, $statusCode);
});

/**
 * API v1
 */
$router->group('/v1', function (Router $router): void {

    $router->get('/health', function (): void {
        $payload = Health::check();
        $dbOk = ($payload['checks']['db'] ?? null) === 'pass';
        $statusCode = $dbOk ? 200 : 503;

        Response::ok([
            'api_version' => 'v1',
            'health'      => $payload,
        ], $statusCode);
    });

    $router->post('/rooms', function (): void {
        $handler = new RoomsHandler(new RoomRepository(DB::pdo()));
        $handler->create();
    });

    $router->get('/rooms', function (): void {
        $handler = new RoomsHandler(new RoomRepository(DB::pdo()));
        $handler->index();
    });

    $router->get('/rooms/{id}', function (string $id): void {
        $handler = new RoomsHandler(new RoomRepository(DB::pdo()));
        $handler->show($id);
    });

    $router->post('/rooms/{id}/close', function (string $id): void {
        $handler = new RoomsHandler(new RoomRepository(DB::pdo()));
        $handler->close($id);
    });

    $router->post('/rooms/{id}/tokens', function (string $id): void {
        $handler = new TokensHandler(
            new RoomRepository(DB::pdo()),
            new RoomTokenRepository(DB::pdo())
        );
        $handler->createForRoom($id);
    });
});

/**
 * Root landing (simple HTML)
 */
$router->get('/', function (): void {
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    echo '<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>VidNect Engine</title>
</head>
<body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:40px;line-height:1.5">
  <h1 style="margin:0 0 10px">VidNect Engine</h1>
  <p style="margin:0 0 16px">Engine is online.</p>
  <ul style="margin:0;padding-left:18px">
    <li><a href="/health">/health</a></li>
    <li><a href="/v1/health">/v1/health</a></li>
  </ul>
</body>
</html>';
});

$router->dispatch();
