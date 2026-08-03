<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VilleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'departement_id' => $this->departement_id,
            'code' => $this->code,
            'nom' => $this->nom,
        ];
    }
}
