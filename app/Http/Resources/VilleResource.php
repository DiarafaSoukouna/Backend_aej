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
            'commune_id' => $this->commune_id,
            'code' => $this->code,
            'nom' => $this->nom,
        ];
    }
}
