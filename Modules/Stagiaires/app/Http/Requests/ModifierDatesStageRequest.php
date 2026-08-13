<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Stagiaires\Models\Stagiaire;

class ModifierDatesStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('modifierDatesStage', Stagiaire::class);
    }

    public function rules(): array
    {
        return [
            'date_debut_stage' => ['required', 'date'],
            'date_fin_stage' => ['required', 'date', 'after:date_debut_stage'],
        ];
    }
}
