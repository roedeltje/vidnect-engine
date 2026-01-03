<?php

declare(strict_types=1);

namespace App\Handlers\V1;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\RoomRepository;

final class RoomsHandler
{
    public function __construct(private RoomRepository $repo) {}

    // POST /v1/rooms
    public function create(): void
    {
        $payload = Request::requireJsonBody();

        $name = null;
        if (isset($payload['name'])) {
            if (!is_string($payload['name'])) {
                Response::error('Field "name" must be a string', 422);
                return;
            }
            $name = trim($payload['name']);
            if ($name === '') $name = null;
        }

        $room = $this->repo->create($name);

        Response::ok($room->toArray(), 201);
    }

    // GET /v1/rooms/{id}
    public function show(string $id): void
    {
        $id = trim($id);

        if ($id === '' || !preg_match('/^[a-f0-9]{16,64}$/i', $id)) {
            Response::error('Invalid room id format', 400);
            return;
        }

        $room = $this->repo->findByRoomId($id);

        if (!$room) {
            Response::error('Room not found', 404);
            return;
        }

        Response::ok($room->toArray(), 200);
    }
}
