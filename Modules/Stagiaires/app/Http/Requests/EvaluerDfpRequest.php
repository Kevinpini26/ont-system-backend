<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Stagiaires\Models\Stagiaire;

class EvaluerDfpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('evaluerEnTantQueDfp', Stagiaire::class);
    }

    public function rules(): array
    {
        $classeGrille = $this->route('stagiaire')->type_stage->classeGrille();

        return [
            'grille' => ['required', 'array'],
            ...$classeGrille::rules('grille'),
        ];
    }
}
