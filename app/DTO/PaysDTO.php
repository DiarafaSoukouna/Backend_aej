<?php

namespace App\DTO;

class PaysDTO
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $code_iso,
        public readonly string $nom,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            code_iso: $data['code_iso'] ?? null,
            nom: $data['nom'],
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
