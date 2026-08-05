<?php

namespace App\DTO;

class CommuneDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $nom,
        public readonly ?int $ville_id,
        public readonly ?int $divisionregionaleaej_id,
        public readonly ?int $guichetemploi_id,
        public readonly ?string $code,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            nom: $data['nom'],
            ville_id: $data['ville_id'] ?? null,
            divisionregionaleaej_id: $data['divisionregionaleaej_id'] ?? null,
            guichetemploi_id: $data['guichetemploi_id'] ?? null,
            code: $data['code'] ?? null,
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
