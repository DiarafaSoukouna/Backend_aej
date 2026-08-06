<?php

namespace App\DTO;

class AgenceRegionaleDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $nom,
        public readonly ?string $latitude,
        public readonly ?string $longitude,
        public readonly ?string $contact,
        public readonly ?string $localisation,
        public readonly ?string $adresse,
        public readonly ?string $telephone,
        public readonly ?string $email,
        public readonly ?int $chef_agence_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            code: $data['code'],
            nom: $data['nom'],
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            contact: $data['contact'] ?? null,
            localisation: $data['localisation'] ?? null,
            adresse: $data['adresse'] ?? null,
            telephone: $data['telephone'] ?? null,
            email: $data['email'] ?? null,
            chef_agence_id: $data['chef_agence_id'] ?? null,
        );
    }

    public static function fromArrayCollection(array $items): array
    {
        return array_map(fn($item) => self::fromArray($item), $items);
    }
}
