<?php

namespace App\DTO;

class VilleDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $departement_id,
        public readonly ?string $code,
        public readonly string $nom,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            departement_id: $data['departement_id'] ?? null,
            code: $data['code'] ?? null,
            nom: $data['nom'],
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
