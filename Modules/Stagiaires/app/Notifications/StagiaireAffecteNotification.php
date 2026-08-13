<?php

namespace Modules\Stagiaires\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Stagiaires\Models\Stagiaire;

class StagiaireAffecteNotification extends Notification
{
    public function __construct(public readonly Stagiaire $stagiaire) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'stagiaire_affecte',
            'stagiaire_id' => $this->stagiaire->id,
            'message' => "Un stagiaire ({$this->stagiaire->nom}) a été affecté à votre direction.",
        ];
    }
}
