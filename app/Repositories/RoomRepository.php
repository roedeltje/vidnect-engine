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

        return new Room($roomId, $name, 'open', $now, $now, null);
    }

    public function findByRoomId(string $roomId): ?Room
    {
        $stmt = $this->pdo->prepare(
            'SELECT room_id, name, status, created_at, updated_at, closed_at
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
            (string)$row['status'],
            (string)$row['created_at'],
            (string)$row['updated_at'],
            $row['closed_at'] !== null ? (string)$row['closed_at'] : null,
        );
    }

    public function list(int $limit = 20, ?int $cursor = null): array
    {
        if ($cursor !== null) {
            $stmt = $this->pdo->prepare(
                'SELECT id, room_id, name, status, created_at, updated_at, closed_at
             FROM rooms
             WHERE id < :cursor
             ORDER BY id DESC
             LIMIT :limit'
            );
            $stmt->bindValue(':cursor', $cursor, PDO::PARAM_INT);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT id, room_id, name, status, created_at, updated_at, closed_at
             FROM rooms
             ORDER BY id DESC
             LIMIT :limit'
            );
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        $nextCursor = null;

        foreach ($rows as $row) {
            $items[] = (new Room(
                (string)$row['room_id'],
                $row['name'] !== null ? (string)$row['name'] : null,
                (string)$row['status'],
                (string)$row['created_at'],
                (string)$row['updated_at'],
                $row['closed_at'] !== null ? (string)$row['closed_at'] : null,
            ))->toArray();

            $nextCursor = (int)$row['id']; // steeds overschrijven → eindigt bij laatste item
        }

        // Als er minder dan limit is, dan is er geen volgende pagina
        if (count($rows) < $limit) {
            $nextCursor = null;
        }

        return [
            'items' => $items,
            'next_cursor' => $nextCursor,
        ];
    }

    public function closeByRoomId(string $roomId): Room
    {
        $now = gmdate('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            "UPDATE rooms
         SET status = 'closed',
             closed_at = :closed_at,
             updated_at = :updated_at
         WHERE room_id = :room_id"
        );

        $stmt->execute([
            'closed_at' => $now,
            'updated_at' => $now,
            'room_id' => $roomId,
        ]);

        // opnieuw ophalen voor consistente output
        return $this->findByRoomId($roomId);
    }
}
