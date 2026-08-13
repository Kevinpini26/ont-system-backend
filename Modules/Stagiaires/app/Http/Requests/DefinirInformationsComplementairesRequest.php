<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Stagiaires\Models\Stagiaire;

class DefinirInformationsComplementairesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gererInformationsComplementaires', Stagiaire::class);
    }

    public function rules(): array
    {
        return [
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'filiere_formation' => ['nullable', 'string', 'max:255'],
            'niveau_formation' => ['nullable', 'string', 'max:255'],
            'maitre_stage' => ['nullable', 'string', 'max:255'],
            'conseiller_stage' => ['nullable', 'string', 'max:255'],
        ];
    }
}
