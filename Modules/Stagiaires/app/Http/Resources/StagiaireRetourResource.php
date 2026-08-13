<?php

namespace Modules\Stagiaires\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Stagiaires\Models\StagiaireRetour */
class StagiaireRetourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'note_encadrement' => $this->note_encadrement,
            'note_missions' => $this->note_missions,
            'note_ambiance' => $this->note_ambiance,
            'commentaire' => $this->commentaire,
            'created_at' => $this->created_at,
        ];
    }
}
