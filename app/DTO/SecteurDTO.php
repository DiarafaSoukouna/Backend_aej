<?php

namespace App\DTO;

class SecteurDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $libelle,
        public readonly string $nom,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            libelle: $data['libelle'],
            nom: $data['nom'],
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
