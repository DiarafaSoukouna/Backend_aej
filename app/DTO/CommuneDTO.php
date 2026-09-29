<?php

namespace App\DTO;

class CommuneDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly ?int $sous_prefecture_id,
        public readonly ?string $code,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            nom: $data['nom'],
            sous_prefecture_id: $data['sous_prefecture_id'] ?? null,
            code: $data['code'] ?? null,
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
