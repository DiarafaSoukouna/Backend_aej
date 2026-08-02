<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjetParameterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'secteuractivites' => SecteurResource::collection($this->secteuractivites),
            'villes' => $this->villes,
            'communes' => $this->communes,
            'divisions' => AgenceRegionaleResource::collection($this->divisions),
        ];
    }
}
