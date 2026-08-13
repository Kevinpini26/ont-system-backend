<?php

use Modules\Kernel\Enums\Poste;

return [
    'name' => 'Kernel',

    /**
     * Postes du rôle agent_circuit_courrier qui contournent le filtrage
     * par direction (DirectionScope) car ils traitent les dossiers de
     * toutes les directions dans le circuit courrier central.
     */
    'circuit_courrier_central_postes' => [
        Poste::RECEPTION->value,
        Poste::PROTOCOLE->value,
        Poste::DGA->value,
        Poste::ASSISTANT_PROTOCOLE->value,
        Poste::ASSISTANT_1->value,
        Poste::ASSISTANT_2->value,
        Poste::ASSISTANT_DGA->value,
        Poste::DG->value,
        Poste::SECRETARIAT_1->value,
        Poste::SECRETARIAT_2->value,
    ],
];
