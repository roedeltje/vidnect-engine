<?php
declare(strict_types=1);

namespace App\Support;

final class Health
{
    public static function check(): array
{
    $dbOk = DB::ping();

    return [
        'status' => $dbOk ? 'pass' : 'fail',
        'checks' => [
            'db' => $dbOk ? 'pass' : 'fail',
        ],
        'service'   => Config::get('app.name', 'VidNect Engine'),
        'env'       => Config::get('app.env', 'production'),
        'version'   => Config::get('app.version', '0.0.0'),
        'timestamp' => gmdate('c'),
    ];
}
}
