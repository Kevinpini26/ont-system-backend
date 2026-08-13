<?php

namespace Modules\Courrier\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Kernel\Http\Resources\UserResource;

/** @mixin \Modules\Courrier\Models\CourrierAnnotation */
class CourrierAnnotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contenu' => $this->contenu,
            'auteur' => new UserResource($this->whenLoaded('auteur')),
            'created_at' => $this->created_at,
        ];
    }
}
