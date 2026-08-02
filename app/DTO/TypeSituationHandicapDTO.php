<?php

namespace App\DTO;

class TypeSituationHandicapDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $code,
        public readonly string $libelle,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            code: $data['code'] ?? null,
            libelle: $data['libelle'],
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
