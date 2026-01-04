<?php

declare(strict_types=1);

namespace App\Handlers\V1;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\RoomRepository;

final class RoomsHandler
{
    private RoomRepository $repo;

    public function __construct(RoomRepository $repo)
    {
        $this->repo = $repo;
    }

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

    public function index(): void
    {
        $limitRaw = $_GET['limit'] ?? 20;
        $cursorRaw = $_GET['cursor'] ?? null;

        // limit validatie
        if (!is_numeric($limitRaw)) {
            Response::error('Invalid limit', 400);
            return;
        }

        $limit = (int) $limitRaw;

        if ($limit < 1 || $limit > 100) {
            Response::error('Limit out of range (1..100)', 422);
            return;
        }

        // cursor validatie
        $cursor = null;
        if ($cursorRaw !== null) {
            if (!is_numeric($cursorRaw)) {
                Response::error('Invalid cursor', 400);
                return;
            }
            $cursor = (int) $cursorRaw;
            if ($cursor < 1) {
                Response::error('Cursor must be >= 1', 422);
                return;
            }
        }

        $result = $this->repo->list($limit, $cursor);

        Response::ok([
            'items' => $result['items'],
            'next_cursor' => $result['next_cursor'],
        ], 200);
    }

    public function close(string $roomId): void
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $roomId)) {
            Response::error('Invalid room id', 400);
            return;
        }

        $room = $this->repo->findByRoomId($roomId);

        if (!$room) {
            Response::error('Room not found', 404);
            return;
        }

        if ($room->status === 'open') {
            $room = $this->repo->closeByRoomId($roomId);
        }

        Response::json([
            'ok' => true,
            'data' => $room->toArray(),
        ]);
    }
}
