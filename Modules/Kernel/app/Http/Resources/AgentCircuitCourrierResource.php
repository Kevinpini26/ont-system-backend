<?php

namespace Modules\Kernel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Vue minimale (id, nom, poste) utilisée pour désigner un collègue du
 * circuit courrier (ex : choix du relecteur) sans exposer les données
 * complètes du compte (réservées à l'administrateur).
 */
class AgentCircuitCourrierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'poste' => $this->poste?->value,
            'poste_label' => $this->poste?->label(),
        ];
    }
}
