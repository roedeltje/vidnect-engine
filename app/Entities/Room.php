<?php

declare(strict_types=1);

namespace App\Entities;

final class Room
{
    public function __construct(
        public string $room_id,
        public ?string $name,
        public string $created_at,
        public string $updated_at,
    ) {}

    public function toArray(): array
    {
        return [
            'room_id' => $this->room_id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
