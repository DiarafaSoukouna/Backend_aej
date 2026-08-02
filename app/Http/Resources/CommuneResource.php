<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommuneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'ville_id' => $this->ville_id,
            'divisionregionaleaej_id' => $this->divisionregionaleaej_id,
        ];
    }
}
