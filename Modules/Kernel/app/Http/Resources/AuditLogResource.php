<?php

namespace Modules\Kernel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'auteur' => $this->whenLoaded('utilisateur', fn () => $this->utilisateur ? [
                'id' => $this->utilisateur->id,
                'name' => $this->utilisateur->name,
            ] : null),
            'sujet_type' => $this->auditable_type,
            'sujet_id' => $this->auditable_id,
            'description' => $this->description,
            'ip_address' => $this->ip_address,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
        ];
    }
}
