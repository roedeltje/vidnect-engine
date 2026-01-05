<?php

declare(strict_types=1);

namespace App\Handlers\V1;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\RoomRepository;
use App\Repositories\RoomTokenRepository;

final class TokensHandler
{
    public function __construct(
        private RoomRepository $rooms,
        private RoomTokenRepository $tokens,
    ) {}

    // POST /v1/rooms/{id}/tokens
    public function createForRoom(string $roomId): void
    {
        $roomId = trim($roomId);

        if ($roomId === '' || !preg_match('/^[a-f0-9]{32}$/i', $roomId)) {
            Response::error('Invalid room id format', 400);
            return;
        }

        $room = $this->rooms->findByRoomId($roomId);
        if (!$room) {
            Response::error('Room not found', 404);
            return;
        }

        if ($room->status !== 'open') {
            Response::error('Room is closed', 409);
            return;
        }

        $payload = Request::requireJsonBody();

        // role
        if (!isset($payload['role'])) {
            Response::error('Field "role" is required', 422);
            return;
        }
        if (!is_string($payload['role'])) {
            Response::error('Field "role" must be a string', 422);
            return;
        }

        $role = trim($payload['role']);
        $allowedRoles = ['host', 'moderator', 'guest'];
        if ($role === '' || !in_array($role, $allowedRoles, true)) {
            Response::error('Field "role" must be one of: host, moderator, guest', 422);
            return;
        }

        // ttl (seconds)
        $ttl = 3600;
        if (isset($payload['ttl'])) {
            if (!is_numeric($payload['ttl'])) {
                Response::error('Field "ttl" must be an integer', 422);
                return;
            }
            $ttl = (int)$payload['ttl'];
        }

        if ($ttl < 60 || $ttl > 86400) {
            Response::error('TTL out of range (60..86400)', 422);
            return;
        }

        $tokenData = $this->tokens->create($roomId, $role, $ttl);
        Response::ok($tokenData, 201);
    }
}
