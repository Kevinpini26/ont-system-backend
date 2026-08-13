<?php

namespace Modules\Public\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Stagiaires\Models\StagiaireLienPublic */
class LienPublicResource extends JsonResource
{
    /**
     * Vue volontairement minimale : juste de quoi afficher le bon
     * formulaire côté public, sans exposer de données internes.
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type->value,
            'valide' => $this->estValide(),
            'stagiaire' => [
                'nom' => $this->stagiaire->nom,
                'direction' => $this->stagiaire->direction?->nom,
                'date_debut_stage' => $this->stagiaire->date_debut_stage?->toDateString(),
                'date_fin_stage' => $this->stagiaire->date_fin_stage?->toDateString(),
            ],
        ];
    }
}
