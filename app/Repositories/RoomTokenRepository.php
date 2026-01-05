<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class RoomTokenRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Create a new stateful room token.
     *
     * Token itself is never stored; only SHA-256 hash is stored.
     * The plaintext token is returned once in the response.
     *
     * @return array{token:string, expires_at:string, role:string, room_id:string}
     */
    public function create(string $roomId, string $role, int $ttlSeconds): array
    {
        $nowTs = time();
        $now = gmdate('Y-m-d H:i:s', $nowTs);
        $expiresAt = gmdate('Y-m-d H:i:s', $nowTs + $ttlSeconds);

        $token = $this->generateToken();
        $tokenHash = hash('sha256', $token);

        $stmt = $this->pdo->prepare(
            'INSERT INTO room_tokens (token_hash, room_id, role, expires_at, created_at)
             VALUES (:token_hash, :room_id, :role, :expires_at, :created_at)'
        );

        $stmt->execute([
            ':token_hash' => $tokenHash,
            ':room_id' => $roomId,
            ':role' => $role,
            ':expires_at' => $expiresAt,
            ':created_at' => $now,
        ]);

        return [
            'room_id' => $roomId,
            'role' => $role,
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    private function generateToken(): string
    {
        // 32 bytes = 256-bit; base64url encoded + prefix for easy recognition.
        $raw = random_bytes(32);
        $b64 = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
        return 'vne_' . $b64;
    }
}
