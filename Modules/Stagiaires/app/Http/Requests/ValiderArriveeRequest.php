<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Stagiaires\Enums\StagiaireTypeStage;
use Modules\Stagiaires\Models\Stagiaire;

class ValiderArriveeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('affecter', Stagiaire::class);
    }

    /**
     * `date_fin_stage` n'est obligatoire que pour un stage académique : un
     * stage professionnel a une durée initiale fixe de 3 mois calculée côté
     * serveur (voir StagiaireCircuitService::validerArrivee()), la valeur
     * envoyée par le client est alors ignorée si présente.
     */
    public function rules(): array
    {
        return [
            'date_debut_stage' => ['required', 'date'],
            'date_fin_stage' => [
                Rule::requiredIf(fn () => $this->route('stagiaire')->type_stage === StagiaireTypeStage::ACADEMIQUE),
                'nullable', 'date', 'after:date_debut_stage',
            ],
        ];
    }
}
