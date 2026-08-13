<?php

namespace Modules\Courrier\Support;

use Modules\Courrier\Contracts\CircuitTransitionRules;
use Modules\Courrier\Enums\CourrierStatut;
use Modules\Kernel\Enums\Poste;

class ConfigCircuitTransitionRules implements CircuitTransitionRules
{
    public function statutSuivant(CourrierStatut $statut, bool $necessiteAvisDg, bool $initieParDg = false): ?CourrierStatut
    {
        $circuit = $this->circuit($necessiteAvisDg, $initieParDg);
        $suivant = config("courrier.circuit_transitions.{$circuit}.{$statut->value}.suivant");

        return $suivant ? CourrierStatut::from($suivant) : null;
    }

    public function postesAutorises(CourrierStatut $statutCourant, bool $necessiteAvisDg, bool $initieParDg = false): array
    {
        $circuit = $this->circuit($necessiteAvisDg, $initieParDg);
        $postes = config("courrier.circuit_transitions.{$circuit}.{$statutCourant->value}.postes", []);

        return array_map(fn (string $poste) => Poste::from($poste), $postes);
    }

    private function circuit(bool $necessiteAvisDg, bool $initieParDg): string
    {
        if ($initieParDg) {
            return 'dg_initie';
        }

        return $necessiteAvisDg ? 'complet' : 'court';
    }

    public function posteDeCreation(): Poste
    {
        return Poste::from(config('courrier.poste_creation'));
    }
}
