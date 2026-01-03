<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Room;
use PDO;

final class RoomRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(?string $name = null): Room
    {
        $roomId = bin2hex(random_bytes(16));
        $now = gmdate('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            'INSERT INTO rooms (room_id, name, created_at, updated_at)
             VALUES (:room_id, :name, :created_at, :updated_at)'
        );

        $stmt->execute([
            ':room_id' => $roomId,
            ':name' => $name,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        return new Room($roomId, $name, $now, $now);
    }

    public function findByRoomId(string $roomId): ?Room
    {
        $stmt = $this->pdo->prepare(
            'SELECT room_id, name, created_at, updated_at
             FROM rooms
             WHERE room_id = :room_id
             LIMIT 1'
        );

        $stmt->execute([':room_id' => $roomId]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Room(
            (string)$row['room_id'],
            $row['name'] !== null ? (string)$row['name'] : null,
            (string)$row['created_at'],
            (string)$row['updated_at'],
        );
    }
}
