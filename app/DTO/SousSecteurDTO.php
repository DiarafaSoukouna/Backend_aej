<?php

namespace App\DTO;

class SousSecteurDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $secteur_id,
        public readonly string $libelle,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            secteur_id: $data['secteur_id'] ?? null,
            libelle: $data['libelle'],
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
