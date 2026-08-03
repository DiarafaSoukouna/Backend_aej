<?php

namespace App\DTO;

class AgenceRegionaleDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $nom,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            code: $data['code'],
            nom: $data['nom'],
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
