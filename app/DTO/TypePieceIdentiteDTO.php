<?php

namespace App\DTO;

class TypePieceIdentiteDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $libelle,
        public readonly ?string $description,
        public readonly ?string $created_at,
        public readonly ?string $updated_at,
        public readonly ?string $migration_key,
        public readonly bool $actif,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            libelle: $data['libelle'],
            description: $data['description'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
            migration_key: $data['migration_key'] ?? null,
            actif: (bool) ($data['actif'] ?? true),
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
