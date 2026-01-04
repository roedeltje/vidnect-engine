<?php

declare(strict_types=1);

namespace App\Entities;

final class Room
{
    public function __construct(
        public string $room_id,
        public ?string $name,
        public string $status,
        public string $created_at,
        public string $updated_at,
        public ?string $closed_at,
    ) {}

    public function toArray(): array
    {
        return [
            'room_id' => $this->room_id,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
        ];
    }
}
