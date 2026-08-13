<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvaluerDirectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('evaluerEnTantQueDirection', $this->route('stagiaire'));
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
