<?php

namespace App\DTO;

class SexeDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $libelle,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            libelle: $data['libelle'],
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
