<?php

namespace Modules\Stagiaires\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Stagiaires\Models\Stagiaire;

class StageEcheanceProcheNotification extends Notification
{
    public function __construct(public readonly Stagiaire $stagiaire) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'stage_echeance_proche',
            'stagiaire_id' => $this->stagiaire->id,
            'message' => "Le stage de {$this->stagiaire->nom} se termine le {$this->stagiaire->date_fin_stage?->toDateString()} (dans 10 jours).",
        ];
    }
}
