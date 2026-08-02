<?php

namespace App\DTO;

class ProjetParameterDTO
{
    public function __construct(
        public readonly array $secteuractivites,
        public readonly array $formejuridiques,
        public readonly array $regions,
        public readonly array $villes,
        public readonly array $communes,
        public readonly array $divisions,
        public readonly array $typeprojets,
        public readonly array $typeprogrammes,
        public readonly array $districts,
        public readonly array $statuts,
    ) {}

    public static function fromArray(array $data): self
    {
        $parameter = $data['parameter'] ?? [];

        return new self(
            secteuractivites: SecteurDTO::fromArrayCollection($parameter['secteuractivites'] ?? []),
            formejuridiques: $parameter['formejuridiques'] ?? [],
            regions: $parameter['regions'] ?? [],
            villes: array_map(fn($item) => ['id' => $item['id'], 'nom' => $item['nom']], $parameter['villes'] ?? []),
            communes: array_map(fn($item) => ['id' => $item['id'], 'nom' => $item['nom']], $parameter['communes'] ?? []),
            divisions: AgenceRegionaleDTO::fromArrayCollection($parameter['divisions'] ?? []),
            typeprojets: $parameter['typeprojets'] ?? [],
            typeprogrammes: $parameter['typeprogrammes'] ?? [],
            districts: $parameter['districts'] ?? [],
            statuts: $parameter['statuts'] ?? [],
        );
    }
}
